<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller  implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage products', only: ['index']),
            new Middleware('permission:create products', only: ['create', 'store']),
            new Middleware('permission:edit products', only: ['edit', 'update']),
            new Middleware('permission:delete products', only: ['destroy']),
             new Middleware('permission:print products', only: ['printInventory']),
            
        ];
    }

 public function index()
    {
        $products = Product::with(['category', 'branches'])->get();

        // الحسابات التجميعية للمخزون
        $totalQuantity = 0;
        $totalPurchaseCost = 0;
        $totalRetailSales = 0;
        $totalWholesaleSales = 0;

        foreach ($products as $product) {
            $qty = $product->branches->sum('pivot.quantity'); // مجموع الكمية المتوفرة في جميع الفروع
            
            $totalQuantity += $qty;
            // استخدام cost_price بدلاً من purchase_price مطابقةً لجدول المنتجات لديك
            $totalPurchaseCost += ($product->cost_price ?? 0) * $qty;
            $totalRetailSales += ($product->selling_price ?? 0) * $qty;
            $totalWholesaleSales += ($product->wholesale_price ?? $product->selling_price ?? 0) * $qty;
        }

        // حساب الأرباح المتوقعة
        $expectedRetailProfit = $totalRetailSales - $totalPurchaseCost;
        $expectedWholesaleProfit = $totalWholesaleSales - $totalPurchaseCost;

        return view('admin.products.index', compact(
            'products',
            'totalQuantity',
            'totalPurchaseCost',
            'totalRetailSales',
            'totalWholesaleSales',
            'expectedRetailProfit',
            'expectedWholesaleProfit'
        ));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'barcode'         => 'nullable|string|unique:products,barcode',
            'cost_price'      => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'unit'            => 'required|string',
            'quantity'        => 'required|integer|min:0',
            'alert_quantity'  => 'nullable|integer|min:1',
        ]);

        $product = Product::create($request->only([
            'name', 'category_id', 'sku', 'barcode', 'cost_price', 'selling_price', 'wholesale_price', 'unit'
        ]));

        $mainBranch = Branch::first();
        if ($mainBranch) {
            $product->branches()->attach($mainBranch->id, [
                'quantity'       => $request->quantity,
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
            'name'            => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'barcode'         => 'nullable|string|unique:products,barcode,' . $product->id,
            'cost_price'      => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'unit'            => 'required|string',
            'quantity'        => 'required|integer|min:0',
            'alert_quantity'  => 'nullable|integer|min:1',
        ]);

        $product->update($request->only([
            'name', 'category_id', 'sku', 'barcode', 'cost_price', 'selling_price', 'wholesale_price', 'unit'
        ]));

        $mainBranch = Branch::first();
        if ($mainBranch) {
            $product->branches()->syncWithoutDetaching([
                $mainBranch->id => [
                    'quantity'       => $request->quantity,
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

    // دالة لطباعة تقرير المخزون
    public function printInventory()
{
    $products = Product::with(['category', 'branches'])->get();
    $company = \App\Models\CompanySetting::first();

    // حساب التجميعات لنهاية التقرير
    $totalQuantity = 0;
    $totalPurchaseCost = 0;
    $totalRetailSales = 0;
    $totalWholesaleSales = 0;

    foreach ($products as $product) {
        $qty = $product->branches->sum('pivot.quantity');
        $totalQuantity += $qty;
        $totalPurchaseCost += ($product->cost_price ?? 0) * $qty;
        $totalRetailSales += ($product->selling_price ?? 0) * $qty;
        $totalWholesaleSales += ($product->wholesale_price ?? $product->selling_price ?? 0) * $qty;
    }

    return view('admin.products.print_inventory', compact(
        'products',
        'company',
        'totalQuantity',
        'totalPurchaseCost',
        'totalRetailSales',
        'totalWholesaleSales'
    ));
}
}