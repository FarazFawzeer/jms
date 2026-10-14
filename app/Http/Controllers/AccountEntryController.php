<?php

namespace App\Http\Controllers;

use App\Models\AccountEntry;
use App\Models\AccountType;
use App\Models\Customer;
use App\Support\Division;
use App\Support\NextCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountEntryController extends Controller
{
    public function index(Request $request)
    {
        $seesAll = Division::seesAll();
        $status = $request->query('status', 'active');
        $direction = $request->query('type');
        $from = $this->date($request->query('from')) ?? now()->startOfMonth();
        $to = $this->date($request->query('to')) ?? now();

        // AccountEntry:: queries are filtered to the user's division automatically (BelongsToDivision)
        $query = AccountEntry::query()
            ->search($request->query('q'))
            ->entryStatus($status)
            ->whereBetween('Ent_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->when(isset(AccountEntry::DIRECTIONS[$direction]), fn ($q) => $q->where('Type', $direction))
            ->when($request->query('account'), fn ($q, $a) => $q->where('add_ded_sys_code', $a))
            ->when($seesAll && $request->query('division'), fn ($q) => $q->where('stock_loc_sys_code', $request->query('division')));

        $totals = (clone $query)->selectRaw("
            SUM(CASE WHEN Type = 'A' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN Type = 'D' THEN amount ELSE 0 END) as expense,
            COUNT(*) as entries")->first();

        $entries = $query
            ->orderByDesc('Ent_date')
            ->orderByDesc('Ent_No')
            ->paginate(15)
            ->withQueryString();

        return view('income-expenses.index', [
            'entries' => $entries,
            'totals' => $totals,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'seesAll' => $seesAll,
            'accounts' => AccountType::query()->orderBy('add_ded_desc')->get(),
            'divisions' => $seesAll ? Division::options() : collect(),
        ]);
    }

    public function create(Request $request)
    {
        $entry = new AccountEntry([
            // "Add Another" keeps the last type, date, account and division
            'Type' => $request->query('type') === AccountEntry::INCOME ? AccountEntry::INCOME : AccountEntry::EXPENSE,
            'Ent_date' => $this->date($request->query('date')) ?? now(),
            'add_ded_sys_code' => $request->query('account'),
            'stock_loc_sys_code' => $request->query('division'),
            'emp_sys_code' => AccountEntry::CASH_PARTY,
            'P_Type' => 'Cash',
        ]);

        return view('income-expenses.create', $this->formData($entry));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $division = Division::forSaving($request->input('division'));

        if (! $division) {
            return response()->json([
                'success' => false,
                'errors' => ['division' => [Division::seesAll()
                    ? 'Choose the division for this entry.'
                    : 'Your login has no division. Ask an admin to set one.']],
            ], 422);
        }

        $entry = DB::transaction(function () use ($data, $division, $request) {
            $entry = AccountEntry::create($data + [
                'comp_code' => AccountEntry::COMPANY,
                'Ent_No' => NextCode::for('POther_InDed_tab', 'Ent_No', 7, ['comp_code' => AccountEntry::COMPANY]),
                'TREnt_No' => '',
                'stock_loc_sys_code' => $division,
                'PStatus' => 'A',
                'OGW' => '0',
                'Add_User' => $request->user()?->name,
                'Add_Date' => now()->format('Y-m-d H:i:s'),
            ]);
            $entry->post();

            return $entry;
        });

        return response()->json([
            'success' => true,
            'message' => $entry->direction_label[0] . ' of ' . number_format($entry->amount, 2) . " saved. Entry No: {$entry->entry_no}",
            'code' => $entry->entry_no,
            'again' => route('admin.income-expenses.create', [
                'type' => $entry->direction,
                'date' => $entry->Ent_date->format('Y-m-d'),
                'account' => $entry->account_code,
                'division' => Division::seesAll() ? $division : null,
            ]),
        ]);
    }

    public function edit(AccountEntry $entry)
    {
        return view('income-expenses.create', $this->formData($entry));
    }

    public function update(Request $request, AccountEntry $entry)
    {
        if (! $entry->isActive()) {
            return response()->json(['success' => false, 'message' => 'This entry is cancelled. Restore it first to change it.'], 422);
        }

        $data = $this->validated($request, $entry);

        DB::transaction(function () use ($entry, $data, $request) {
            $old = $entry->posting(); // find the cash book row before the amount / account changes

            $entry->update($data + [
                'change_user' => $request->user()?->name,
                'change_date' => now()->format('Y-m-d H:i:s'),
            ]);

            // Some old entries were never posted by the old app; leave the cash book history as it was for those.
            if ($old) {
                $entry->post($old);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Entry {$entry->entry_no} updated.",
            'code' => $entry->entry_no,
        ]);
    }

    /** Cancel / restore instead of deleting. A cancelled entry is taken out of the cash book. */
    public function toggleStatus(Request $request, AccountEntry $entry)
    {
        DB::transaction(function () use ($entry, $request) {
            if ($entry->isActive()) {
                AccountEntry::unpost($entry->posting());
            } elseif (! $entry->posting()) {
                $entry->post();
            }

            $entry->update([
                'PStatus' => $entry->isActive() ? 'I' : 'A',
                'change_user' => $request->user()?->name,
                'change_date' => now()->format('Y-m-d H:i:s'),
            ]);
        });

        return response()->json([
            'success' => true,
            'active' => $entry->isActive(),
            'message' => $entry->isActive() ? "Entry {$entry->entry_no} restored." : "Entry {$entry->entry_no} cancelled.",
        ]);
    }

    private function formData(AccountEntry $entry): array
    {
        $seesAll = Division::seesAll();

        return [
            'entry' => $entry,
            'seesAll' => $seesAll,
            'accounts' => AccountType::options(array_filter([$entry->account_code])),
            'parties' => Customer::query()
                ->where(fn ($q) => $q->where('Status', '<>', 'I')->orWhere('Cust_Sys_Code', $entry->party_code ?: ''))
                ->orderBy('Cust_Name')
                ->get(['Cust_Sys_Code', 'Cust_Name']),
            'divisions' => $seesAll ? Division::options(array_filter([$entry->division_code])) : collect(),
        ];
    }

    private function validated(Request $request, ?AccountEntry $entry = null): array
    {
        $request->merge([
            'description' => mb_strtoupper(trim((string) $request->input('description'))),
        ]);

        $v = $request->validate([
            'type' => ['required', Rule::in([AccountEntry::INCOME, AccountEntry::EXPENSE])],
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'account' => [
                'required', 'string', 'size:4',
                Rule::exists('PAdd_Ded_Types_tab', 'add_ded_sys_code')->where('comp_code', AccountType::COMPANY),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'party' => [
                'required', 'string', 'max:6',
                Rule::exists('PCust_Registration_Tab', 'Cust_Sys_Code')->where('Comp_Code', Customer::COMPANY),
            ],
            'pay_mode' => ['required', Rule::in(array_keys(AccountEntry::PAY_MODES))],
            'description' => ['required', 'string', 'max:50'],
        ], [
            'type.required' => 'Choose Income or Expense.',
            'date.required' => 'Enter the date.',
            'date.before_or_equal' => 'The date cannot be in the future.',
            'account.required' => 'Choose the account type.',
            'account.exists' => 'Choose an account type from the list.',
            'amount.required' => 'Enter the amount.',
            'amount.gt' => 'Amount must be more than 0.',
            'party.required' => 'Choose who paid / was paid (CASH if nobody).',
            'party.exists' => 'Choose a name from the list.',
            'description.required' => 'Enter a short description.',
        ]);

        $account = AccountType::find($v['account']);
        if (! $account->allows($v['type'])) {
            abort(response()->json([
                'success' => false,
                'errors' => ['account' => [$account->display_name . ' is an ' . strtolower($account->category) . ' account.']],
            ], 422));
        }
        if (! $account->isActive() && $account->code !== $entry?->account_code) {
            abort(response()->json([
                'success' => false,
                'errors' => ['account' => [$account->display_name . ' is inactive.']],
            ], 422));
        }

        $date = Carbon::createFromFormat('Y-m-d', $v['date'])->startOfDay();

        return [
            'Type' => $v['type'],
            'Ent_date' => $date->format('Y-m-d'),
            'year_month' => $date->format('Y-m'),
            'add_ded_sys_code' => $account->add_ded_sys_code,
            'amount' => round((float) $v['amount'], 2),
            'emp_sys_code' => $v['party'],
            'Emp_Name' => mb_substr(trim((string) Customer::find($v['party'])?->Cust_Name), 0, 100),
            'P_Type' => $v['pay_mode'],
            'misc_desc' => $v['description'],
        ];
    }

    private function date(?string $value): ?Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m-d', $value) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
