<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'branches'])->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'barcode' => 'nullable|string|unique:products,barcode',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::create($request->only([
            'name', 'category_id', 'sku', 'barcode', 'cost_price', 'selling_price', 'unit'
        ]));

        $mainBranch = Branch::first();
        if ($mainBranch) {
            $product->branches()->attach($mainBranch->id, [
                'quantity' => $request->quantity,
                'alert_quantity' => $request->alert_quantity ?? 5,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'تم إضافة المنتج والمخزون بنجاح');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $mainBranch = Branch::first();
        
        // جلب الكمية وحد التنبيه الخاصة بالفرع الرئيسي
        $branchData = $mainBranch ? $product->branches()->where('branch_id', $mainBranch->id)->first() : null;
        $quantity = $branchData ? $branchData->pivot->quantity : 0;
        $alertQuantity = $branchData ? $branchData->pivot->alert_quantity : 5;

        return view('admin.products.edit', compact('product', 'categories', 'quantity', 'alertQuantity'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:1',
        ]);

        $product->update($request->only([
            'name', 'category_id', 'sku', 'barcode', 'cost_price', 'selling_price', 'unit'
        ]));

        $mainBranch = Branch::first();
        if ($mainBranch) {
            $product->branches()->syncWithoutDetaching([
                $mainBranch->id => [
                    'quantity' => $request->quantity,
                    'alert_quantity' => $request->alert_quantity ?? 5,
                ]
            ]);     
        }

        return redirect()->route('products.index')->with('success', 'تم تحديث بيانات المنتج والمخزون بنجاح');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'تم حذف المنتج بنجاح');
    }
}