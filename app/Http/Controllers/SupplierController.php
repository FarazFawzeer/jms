<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Support\NextCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');

        $suppliers = Supplier::query()
            ->search($request->query('q'))
            ->when($status === 'active', fn ($q) => $q->where('status', '<>', 'I'))
            ->when($status === 'inactive', fn ($q) => $q->where('status', 'I'))
            ->orderByDesc('Supliers_Sys_Code')
            ->paginate(15)
            ->withQueryString();

        $categories = Supplier::categoryNamesFor($suppliers->pluck('Supliers_Sys_Code')->all());

        $counts = [
            'all' => Supplier::count(),
            'active' => Supplier::where('status', '<>', 'I')->count(),
            'inactive' => Supplier::where('status', 'I')->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'categories', 'counts', 'status'));
    }

    public function create()
    {
        $supplier = new Supplier();
        $nextCode = NextCode::for('pSuppliers_Register_Mast_tab', 'Supliers_Sys_Code', 6, ['Comp_Code' => Supplier::COMPANY]);
        $selected = [];
        $categoryOptions = Supplier::categoryOptions();

        return view('suppliers.create', compact('supplier', 'nextCode', 'selected', 'categoryOptions'));
    }

    public function store(Request $request)
    {
        [$data, $categories] = $this->validated($request);

        $supplier = DB::transaction(function () use ($data, $categories, $request) {
            $code = NextCode::for('pSuppliers_Register_Mast_tab', 'Supliers_Sys_Code', 6, ['Comp_Code' => Supplier::COMPANY]);

            $data['Suppliers_User_Code'] ??= $code;

            $supplier = Supplier::create($data + [
                'Comp_Code' => Supplier::COMPANY,
                'Supliers_Sys_Code' => $code,
                'status' => 'A',
                'Cus_Balance' => 0,
                'Add_User' => $request->user()?->name,
                'Add_date' => now(),
            ]);
            $supplier->syncCategories($categories);

            return $supplier;
        });

        return response()->json([
            'success' => true,
            'message' => "{$supplier->display_name} saved. Supplier No: {$supplier->code}",
            'code' => $supplier->code,
        ]);
    }

    public function edit(Supplier $supplier)
    {
        $selected = $supplier->categoryCodes();
        $categoryOptions = Supplier::categoryOptions($selected);

        return view('suppliers.create', ['supplier' => $supplier, 'nextCode' => null, 'selected' => $selected, 'categoryOptions' => $categoryOptions]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        [$data, $categories] = $this->validated($request, $supplier);

        DB::transaction(function () use ($supplier, $data, $categories, $request) {
            $data['Suppliers_User_Code'] ??= $supplier->code;

            $supplier->update($data + [
                'Change_User' => $request->user()?->name,
                'Change_Date' => now(),
            ]);
            $supplier->syncCategories($categories);
        });

        return response()->json([
            'success' => true,
            'message' => "{$supplier->display_name} updated.",
            'code' => $supplier->code,
        ]);
    }

    /** Deactivate / activate instead of deleting: old GRNs still point to this supplier. */
    public function toggleStatus(Request $request, Supplier $supplier)
    {
        $supplier->update([
            'status' => $supplier->isActive() ? 'I' : 'A',
            'Change_User' => $request->user()?->name,
            'Change_Date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'active' => $supplier->isActive(),
            'message' => $supplier->isActive() ? 'Supplier is active again.' : 'Supplier deactivated.',
        ]);
    }

    /** Warn while typing if this phone number is already registered for another supplier. */
    public function checkPhone(Request $request)
    {
        $phone = Customer::normalizePhone($request->query('phone'));
        if (! $phone || strlen($phone) < 11) {
            return response()->json(['match' => null]);
        }

        $match = Supplier::query()
            ->where(fn ($q) => $q->where('Tel1', $phone)->orWhere('Tel2', $phone))
            ->when($request->query('except'), fn ($q, $code) => $q->where('Supliers_Sys_Code', '<>', $code))
            ->first();

        return response()->json(['match' => $match ? [
            'code' => $match->code,
            'name' => $match->display_name,
            'url' => route('admin.suppliers.edit', $match->code),
        ] : null]);
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $request->merge([
            'mobile' => Customer::normalizePhone($request->input('mobile')),
            'phone2' => Customer::normalizePhone($request->input('phone2')),
            'user_code' => mb_strtoupper(trim((string) $request->input('user_code'))) ?: null,
            'email' => mb_strtolower(trim((string) $request->input('email'))) ?: null,
        ]);

        $v = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'user_code' => [
                'nullable', 'string', 'max:15', 'regex:/^[A-Z0-9\-\/]+$/',
                Rule::unique('pSuppliers_Register_Mast_tab', 'Suppliers_User_Code')
                    ->where('Comp_Code', Supplier::COMPANY)
                    ->ignore($supplier?->Supliers_Sys_Code, 'Supliers_Sys_Code'),
            ],
            'contact' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'regex:/^94\d{9}$/'],
            'phone2' => ['nullable', 'regex:/^94\d{9}$/'],
            'email' => ['nullable', 'email', 'max:50'],
            'address' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:50'],
            'vat_no' => ['nullable', 'string', 'max:15'],
            'categories' => ['nullable', 'array'],
            'categories.*' => [
                'string', 'size:3',
                Rule::exists(Supplier::CATEGORY_TABLE, 'item_grp_sys_code')
                    ->where('comp_code', Supplier::COMPANY)
                    ->where('item_type_sys_Code', Supplier::ITEM_TYPE),
            ],
        ], [
            'name.required' => 'Please enter the supplier name.',
            'user_code.unique' => 'This supplier code is already used.',
            'user_code.regex' => 'Use only letters, numbers, - or /.',
            'mobile.regex' => 'Enter a 10 digit mobile number, like 077 123 4567.',
            'phone2.regex' => 'Enter a 10 digit phone number, like 077 123 4567.',
            'email.email' => 'Enter a valid email address.',
            'categories.*.exists' => 'Choose categories from the list.',
        ]);

        $up = fn ($s) => $s === null ? null : mb_strtoupper(trim($s));

        return [[
            'Suppliers_Name' => $up($v['name']),
            'Suppliers_User_Code' => $v['user_code'] ?? null,
            'Contract_Person' => $up($v['contact'] ?? null),
            'Tel1' => $v['mobile'] ?? null,
            'Tel2' => $v['phone2'] ?? null,
            'Email' => $v['email'] ?? null,
            'Add1' => $up($v['address'] ?? null),
            'Add3' => $up($v['city'] ?? null),
            'Vat_No' => $up($v['vat_no'] ?? null),
        ], $v['categories'] ?? []];
    }
}
