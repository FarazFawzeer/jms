<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\NextCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');

        $categories = Category::query()
            ->search($request->query('q'))
            ->when($status === 'active', fn ($q) => $q->where('status', '<>', 'I'))
            ->when($status === 'inactive', fn ($q) => $q->where('status', 'I'))
            ->orderBy('item_grp_desc')
            ->paginate(15)
            ->withQueryString();

        // pieces in stock per category, for the logged-in user's division only
        $stock = Product::query()
            ->stockStatus('stock')
            ->whereIn('item_grp_sys_code', $categories->pluck('item_grp_sys_code')->all() ?: [''])
            ->groupBy('item_grp_sys_code')
            ->selectRaw('item_grp_sys_code as grp, COUNT(*) as pieces, SUM(item_size) as weight')
            ->get()
            ->keyBy(fn ($r) => trim($r->grp));

        $counts = [
            'all' => Category::count(),
            'active' => Category::where('status', '<>', 'I')->count(),
            'inactive' => Category::where('status', 'I')->count(),
        ];

        return view('categories.index', compact('categories', 'stock', 'counts', 'status'));
    }

    public function create()
    {
        $category = new Category();
        $nextCode = $this->nextCode();

        return view('categories.create', compact('category', 'nextCode'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $category = DB::transaction(function () use ($data, $request) {
            $code = $this->nextCode();

            if ((int) $code > Category::MAX_CODE) {
                abort(response()->json([
                    'success' => false,
                    'errors' => ['name' => ['The category list is full (no. 099 reached). Re-use an inactive category instead.']],
                ], 422));
            }

            return Category::create($data + [
                'comp_code' => Category::COMPANY,
                'item_type_sys_Code' => Category::ITEM_TYPE,
                'item_grp_sys_code' => $code,
                'status' => 'A',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "{$category->display_name} saved. Category No: {$category->code}",
            'code' => $category->code,
        ]);
    }

    public function edit(Category $category)
    {
        return view('categories.create', ['category' => $category, 'nextCode' => null]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => "{$category->display_name} updated.",
            'code' => $category->code,
        ]);
    }

    /** Deactivate / activate instead of deleting: old items and bills still point to this category. */
    public function toggleStatus(Category $category)
    {
        $category->update(['status' => $category->isActive() ? 'I' : 'A']);

        return response()->json([
            'success' => true,
            'active' => $category->isActive(),
            'message' => $category->isActive() ? 'Category is active again.' : 'Category deactivated. Existing items keep it.',
        ]);
    }

    private function nextCode(): string
    {
        return NextCode::for('PItem_grp_tab', 'item_grp_sys_code', 3, [
            'comp_code' => Category::COMPANY,
            'item_type_sys_Code' => Category::ITEM_TYPE,
        ]);
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $request->merge([
            'name' => mb_strtoupper(trim((string) $request->input('name'))),
            'short_code' => mb_strtoupper(trim((string) $request->input('short_code'))) ?: null,
        ]);

        $unique = fn ($column) => Rule::unique('PItem_grp_tab', $column)
            ->where('comp_code', Category::COMPANY)
            ->where('item_type_sys_Code', Category::ITEM_TYPE)
            ->ignore($category?->item_grp_sys_code, 'item_grp_sys_code');

        $v = $request->validate([
            'name' => ['required', 'string', 'max:50', $unique('item_grp_desc')],
            'short_code' => ['required', 'string', 'max:3', 'regex:/^[A-Z0-9\-]+$/', $unique('SH_Des')],
        ], [
            'name.required' => 'Please enter the category name.',
            'name.unique' => 'This category already exists.',
            'short_code.required' => 'Enter a short code (up to 3 letters) for tags.',
            'short_code.unique' => 'This short code is already used by another category.',
            'short_code.regex' => 'Use only letters, numbers or -.',
        ]);

        return [
            'item_grp_desc' => $v['name'],
            'SH_Des' => $v['short_code'],
        ];
    }
}
