<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Support\NextCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');

        $customers = Customer::query()
            ->search($request->query('q'))
            ->when($status === 'active', fn ($q) => $q->where('Status', '<>', 'I'))
            ->when($status === 'inactive', fn ($q) => $q->where('Status', 'I'))
            ->orderByDesc('Cust_Sys_Code')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Customer::count(),
            'active' => Customer::where('Status', '<>', 'I')->count(),
            'inactive' => Customer::where('Status', 'I')->count(),
        ];

        return view('customers.index', compact('customers', 'counts', 'status'));
    }

    public function create()
    {
        $customer = new Customer(['Email' => 'Retails', 'Contract_Person' => 'Mr.']);
        $nextCode = NextCode::for('PCust_Registration_Tab', 'Cust_Sys_Code', 6, ['Comp_Code' => Customer::COMPANY]);

        return view('customers.create', compact('customer', 'nextCode'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $customer = DB::transaction(function () use ($data, $request) {
            $code = NextCode::for('PCust_Registration_Tab', 'Cust_Sys_Code', 6, ['Comp_Code' => Customer::COMPANY]);

            return Customer::create($data + [
                'Comp_Code' => Customer::COMPANY,
                'Cust_Sys_Code' => $code,
                'Cust_User_Code' => $code,
                'Status' => 'A',
                'Cus_Balance' => 0,
                'VatNo' => 0,
                'srCust_Sys_Code' => '000001',
                'Add_User' => $request->user()?->name,
                'Add_Date' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "{$customer->display_name} saved. Customer No: {$customer->code}",
            'code' => $customer->code,
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('customers.create', ['customer' => $customer, 'nextCode' => null]);
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request) + [
            'Change_User' => $request->user()?->name,
            'Change_Date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$customer->display_name} updated.",
            'code' => $customer->code,
        ]);
    }

    /** Deactivate / activate instead of deleting: old bills and orders still point to this customer. */
    public function toggleStatus(Request $request, Customer $customer)
    {
        $customer->update([
            'Status' => $customer->isActive() ? 'I' : 'A',
            'Change_User' => $request->user()?->name,
            'Change_Date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'active' => $customer->isActive(),
            'message' => $customer->isActive() ? 'Customer is active again.' : 'Customer deactivated.',
        ]);
    }

    /** Warn while typing if this phone number is already registered. */
    public function checkPhone(Request $request)
    {
        $phone = Customer::normalizePhone($request->query('phone'));
        if (! $phone || strlen($phone) < 11) {
            return response()->json(['match' => null]);
        }

        $match = Customer::query()
            ->where(fn ($q) => $q->where('Tel1', $phone)->orWhere('Tel2', $phone))
            ->when($request->query('except'), fn ($q, $code) => $q->where('Cust_Sys_Code', '<>', $code))
            ->first();

        return response()->json(['match' => $match ? [
            'code' => $match->code,
            'name' => $match->display_name,
            'url' => route('admin.customers.edit', $match->code),
        ] : null]);
    }

    private function validated(Request $request): array
    {
        $request->merge([
            'mobile' => Customer::normalizePhone($request->input('mobile')),
            'phone2' => Customer::normalizePhone($request->input('phone2')),
        ]);

        $v = $request->validate([
            'title' => ['nullable', Rule::in(Customer::TITLES)],
            'name' => ['required', 'string', 'max:200'],
            'mobile' => ['nullable', 'regex:/^94\d{9}$/'],
            'phone2' => ['nullable', 'regex:/^94\d{9}$/'],
            'type' => ['required', Rule::in(array_keys(Customer::TYPES))],
            'address' => ['nullable', 'string', 'max:100'],
            'address2' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', Rule::in(array_keys(Customer::RELIGIONS))],
        ], [
            'name.required' => 'Please enter the customer name.',
            'mobile.regex' => 'Enter a 10 digit mobile number, like 077 123 4567.',
            'phone2.regex' => 'Enter a 10 digit phone number, like 077 123 4567.',
            'type.required' => 'Choose Retail or Ledger.',
        ]);

        $up = fn ($s) => $s === null ? null : mb_strtoupper(trim($s));

        return [
            'Contract_Person' => $v['title'] ?? null,
            'Cust_Name' => $up($v['name']),
            'Tel1' => $v['mobile'] ?? null,
            'Tel2' => $v['phone2'] ?? null,
            'Email' => $v['type'],
            'Add1' => $up($v['address'] ?? null),
            'Add2' => $up($v['address2'] ?? null),
            'Add3' => $up($v['city'] ?? null),
            'Fax' => $v['religion'] ?? null,
        ];
    }
}
