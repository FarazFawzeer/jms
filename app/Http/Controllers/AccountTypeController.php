<?php

namespace App\Http\Controllers;

use App\Models\AccountEntry;
use App\Models\AccountType;
use App\Support\NextCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountTypeController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');
        $category = $request->query('category');

        $types = AccountType::query()
            ->search($request->query('q'))
            ->when(isset(AccountType::CATEGORIES[$category]), fn ($q) => $q->where('Acc_Category', $category))
            ->when($status === 'active', fn ($q) => $q->where('status', '<>', 'I'))
            ->when($status === 'inactive', fn ($q) => $q->where('status', 'I'))
            ->orderBy('add_ded_desc')
            ->paginate(15)
            ->withQueryString();

        // entries per account type, for the logged-in user's division only
        $usage = AccountEntry::query()
            ->entryStatus('active')
            ->whereIn('add_ded_sys_code', $types->pluck('add_ded_sys_code')->all() ?: [''])
            ->groupBy('add_ded_sys_code')
            ->selectRaw("add_ded_sys_code as code, COUNT(*) as entries,
                SUM(CASE WHEN Type = 'A' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN Type = 'D' THEN amount ELSE 0 END) as expense")
            ->get()
            ->keyBy(fn ($r) => trim($r->code));

        return view('account-types.index', compact('types', 'usage', 'status'));
    }

    public function create()
    {
        $type = new AccountType(['Acc_Category' => 'Expense']);
        $nextCode = $this->nextCode();

        return view('account-types.create', compact('type', 'nextCode'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $type = DB::transaction(function () use ($data) {
            $code = $this->nextCode();

            if ((int) $code > 9999) {
                abort(response()->json([
                    'success' => false,
                    'errors' => ['name' => ['The account type list is full. Re-use an inactive one instead.']],
                ], 422));
            }

            $order = (int) DB::table('PAdd_Ded_Types_tab')->where('comp_code', AccountType::COMPANY)->max('payslip_loc') + 1;

            // the other columns are filled the same way the old app fills them
            return AccountType::create($data + [
                'comp_code' => AccountType::COMPANY,
                'add_ded_sys_code' => $code,
                'add_ded_sub_type' => null,
                'rate' => 0,
                'status' => 'A',
                'payslip_loc' => $order,
                'paysheet_loc' => 0,
                'type' => 'GSD',
                'visibal_type' => 'V',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "{$type->display_name} saved. Account No: {$type->code}",
            'code' => $type->code,
        ]);
    }

    public function edit(AccountType $accountType)
    {
        return view('account-types.create', ['type' => $accountType, 'nextCode' => null]);
    }

    public function update(Request $request, AccountType $accountType)
    {
        $data = $this->validated($request, $accountType);

        // Changing to Income or Expense only is blocked when entries of the other kind already use it.
        $blocked = match ($data['Acc_Category']) {
            'Income' => AccountEntry::EXPENSE,
            'Expense' => AccountEntry::INCOME,
            default => null,
        };
        if ($blocked && AccountEntry::withoutGlobalScope('division')
            ->where('add_ded_sys_code', $accountType->add_ded_sys_code)->where('Type', $blocked)->exists()) {
            return response()->json([
                'success' => false,
                'errors' => ['category' => ['This account already has ' . strtolower(AccountEntry::DIRECTIONS[$blocked][0])
                    . ' entries. Choose "Income & Expense" instead.']],
            ], 422);
        }

        $accountType->update($data);

        return response()->json([
            'success' => true,
            'message' => "{$accountType->display_name} updated.",
            'code' => $accountType->code,
        ]);
    }

    /** Deactivate / activate instead of deleting: old entries and the cash book still point to it. */
    public function toggleStatus(AccountType $accountType)
    {
        $accountType->update(['status' => $accountType->isActive() ? 'I' : 'A']);

        return response()->json([
            'success' => true,
            'active' => $accountType->isActive(),
            'message' => $accountType->isActive() ? 'Account type is active again.' : 'Account type deactivated. Existing entries keep it.',
        ]);
    }

    private function nextCode(): string
    {
        return NextCode::for('PAdd_Ded_Types_tab', 'add_ded_sys_code', 4, ['comp_code' => AccountType::COMPANY]);
    }

    private function validated(Request $request, ?AccountType $type = null): array
    {
        $request->merge(['name' => mb_strtoupper(trim((string) $request->input('name')))]);

        $v = $request->validate([
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('PAdd_Ded_Types_tab', 'add_ded_desc')
                    ->where('comp_code', AccountType::COMPANY)
                    ->ignore($type?->add_ded_sys_code, 'add_ded_sys_code'),
            ],
            'category' => ['required', Rule::in(array_keys(AccountType::CATEGORIES))],
        ], [
            'name.required' => 'Please enter the account name.',
            'name.unique' => 'This account type already exists.',
            'category.required' => 'Choose Income, Expense or both.',
            'category.in' => 'Choose Income, Expense or both.',
        ]);

        $expense = $v['category'] === 'Expense';

        return [
            'add_ded_desc' => $v['name'],
            'Acc_Category' => $v['category'],
            // keep the old app's columns in step
            'operation' => $expense ? 'D' : 'A',
            'add_ded_type' => $expense ? 'VarD' : 'VarA',
            'Short_Desc' => $expense ? 'OTHD' : 'OTHA',
        ];
    }
}
