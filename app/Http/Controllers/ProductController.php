<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\Division;
use App\Support\NextCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'stock');
        $seesAll = Division::seesAll();

        // Product:: queries are filtered to the user's division automatically (BelongsToDivision)
        $query = Product::query()
            ->search($request->query('q'))
            ->stockStatus($status)
            ->when($request->query('category'), fn ($q, $c) => $q->where('item_grp_sys_code', $c))
            ->when($request->query('unit'), fn ($q, $u) => $q->where('unit_code', $u))
            ->when($seesAll && $request->query('division'), fn ($q, $d) => $q->where('stock_loc_sys_code', $request->query('division')));

        $totals = (clone $query)->selectRaw('COUNT(*) as pieces, SUM(item_size) as weight')->first();

        $products = $query
            ->orderBy('item_grp_sys_code')
            ->orderByDesc('item_sys_code')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'totals' => $totals,
            'status' => $status,
            'seesAll' => $seesAll,
            'categories' => Category::options(),
            'units' => Product::unitOptions(),
            'divisions' => $seesAll ? Division::options() : collect(),
        ]);
    }

    public function create(Request $request)
    {
        $product = new Product([
            // "Add Another" keeps the last category / karat / division / supplier
            'item_grp_sys_code' => $request->query('category'),
            'unit_code' => $request->query('unit'),
            'stock_loc_sys_code' => $request->query('division'),
            'TagNo' => $request->query('supplier'),
        ]);

        return view('products.create', $this->formData($product));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $division = Division::forSaving($request->input('division'));

        if (! $division) {
            return response()->json([
                'success' => false,
                'errors' => ['division' => [Division::seesAll()
                    ? 'Choose the division this item belongs to.'
                    : 'Your login has no division. Ask an admin to set one.']],
            ], 422);
        }

        $product = DB::transaction(function () use ($data, $division, $request) {
            $group = $data['item_grp_sys_code'];
            $itemNo = NextCode::for('PItems_tab', 'item_sys_code', 4, [
                'comp_code' => Product::COMPANY,
                'item_type_sys_Code' => Product::ITEM_TYPE,
                'item_grp_sys_code' => $group,
            ]);

            if ((int) $itemNo > 9999) {
                abort(response()->json([
                    'success' => false,
                    'errors' => ['category' => ['This category has reached item no. 9999.']],
                ], 422));
            }

            $barcode = substr($group, -2) . $itemNo;
            if (DB::table('PItems_tab')->where('comp_code', Product::COMPANY)->where('BarCode', $barcode)->exists()) {
                abort(response()->json([
                    'success' => false,
                    'errors' => ['category' => ["Barcode {$barcode} is already used. Please contact support."]],
                ], 422));
            }

            return Product::create($data + [
                'comp_code' => Product::COMPANY,
                'item_type_sys_Code' => Product::ITEM_TYPE,
                'item_sys_code' => $itemNo,
                'full_item_code' => Product::ITEM_TYPE . $group . $itemNo,
                'BarCode' => $barcode,
                'Part_No' => $itemNo,
                'stock_loc_sys_code' => $division,
                're_order_levl' => 1,
                're_order_qty' => 0,
                'status' => 'A',
                'LastPrice' => 0,
                'Sel_Price' => 0,
                'SoldType' => 'N',
                'Ap_BillNo' => '0',
                'Item_PS' => '0',
                'Item_TX' => '01',
                'Add_User' => $request->user()?->name,
                'Add_Date' => now()->format('d/M/Y'), // old app stores dates as text: 10/Jul/2026
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "{$product->display_name} saved. Barcode: {$product->barcode_no}",
            'code' => $product->code,
            'again' => route('admin.products.create', [
                'category' => $product->category_code,
                'unit' => trim((string) $product->unit_code),
                'division' => Division::seesAll() ? $division : null,
                'supplier' => trim((string) $product->TagNo) ?: null,
            ]),
        ]);
    }

    public function edit(Product $product)
    {
        return view('products.create', $this->formData($product));
    }

    public function update(Request $request, Product $product)
    {
        if (! $product->isEditable()) {
            return response()->json(['success' => false, 'message' => 'This item is already sold, so it cannot be changed.'], 422);
        }

        // category, codes and division stay as they are (the barcode and stock balances depend on them)
        $data = collect($this->validated($request, $product))->except('item_grp_sys_code')->all();

        $product->update($data + [
            'Change_User' => $request->user()?->name,
            'Chage_Date' => now()->format('d/M/Y'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$product->display_name} ({$product->barcode_no}) updated.",
            'code' => $product->code,
        ]);
    }

    /** Deactivate / activate an unsold item instead of deleting it. */
    public function toggleStatus(Request $request, Product $product)
    {
        if (! $product->inStock()) {
            return response()->json(['success' => false, 'message' => 'Sold items cannot be deactivated.'], 422);
        }

        $product->update([
            'status' => $product->isActive() ? 'I' : 'A',
            'Change_User' => $request->user()?->name,
            'Chage_Date' => now()->format('d/M/Y'),
        ]);

        return response()->json([
            'success' => true,
            'active' => $product->isActive(),
            'message' => $product->isActive() ? 'Item is active again.' : 'Item deactivated.',
        ]);
    }

    private function formData(Product $product): array
    {
        $seesAll = Division::seesAll();

        return [
            'product' => $product,
            'seesAll' => $seesAll,
            'categories' => Category::options(array_filter([$product->category_code])),
            'units' => Product::unitOptions(),
            'divisions' => $seesAll ? Division::options(array_filter([$product->division_code])) : collect(),
            'suppliers' => Supplier::query()->where('status', '<>', 'I')->orderBy('Suppliers_Name')->get(),
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $request->merge([
            'description' => mb_strtoupper(trim((string) $request->input('description'))) ?: null,
        ]);

        $v = $request->validate([
            'category' => [
                $product ? 'nullable' : 'required', 'string', 'size:3',
                Rule::exists('PItem_grp_tab', 'item_grp_sys_code')
                    ->where('comp_code', Category::COMPANY)
                    ->where('item_type_sys_Code', Category::ITEM_TYPE),
            ],
            'unit' => ['required', 'string', 'max:2', Rule::exists('MUnitOfMesure', 'unit_code')->where('comp_code', Product::COMPANY)],
            'description' => ['nullable', 'string', 'max:100'],
            'weight' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'stone_weight' => ['nullable', 'numeric', 'min:0', 'lt:weight'],
            'supplier' => ['nullable', 'string', 'max:8'],
        ], [
            'category.required' => 'Choose a category.',
            'category.exists' => 'Choose a category from the list.',
            'unit.required' => 'Choose the karat / metal.',
            'weight.required' => 'Enter the weight in grams.',
            'weight.gt' => 'Weight must be more than 0.',
            'stone_weight.lt' => 'Stone weight must be less than the total weight.',
        ]);

        $group = $v['category'] ?? $product?->category_code;
        $description = $v['description']
            ?? mb_strtoupper(Category::find($group)?->display_name ?? '');

        return [
            'item_grp_sys_code' => $group,
            'unit_code' => $v['unit'],
            'item_desc' => $description,
            'item_size' => round((float) $v['weight'], 3),
            'U_Price' => round((float) $v['weight'], 3), // the old app keeps the weight here too
            'Stone_weight' => round((float) ($v['stone_weight'] ?? 0), 3),
            'TagNo' => $v['supplier'] ?? '0',
        ];
    }
}
