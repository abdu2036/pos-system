<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::with('branches')->get();
        return view('admin.pos.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,transfer',
        ]);

        $mainBranch = Branch::first();

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $itemsToInsert = [];

            // جلب كافة المنتجات المطلوبة دفعة واحدة لتحسين الأداء
            $productIds = collect($request->items)->pluck('product_id');
            $products = Product::with('branches')->whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = $item['quantity'];

                if (!$product) {
                    throw new \Exception("المنتج المطلوب غير موجود.");
                }

                // التحقق من التوفر في المخزون
                if ($mainBranch) {
                    $branchProduct = $product->branches->where('id', $mainBranch->id)->first();
                    $currentQty = $branchProduct ? $branchProduct->pivot->quantity : 0;

                    if ($currentQty < $quantity) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "الكمية المطلوبة للمنتج ({$product->name}) غير متوفرة في المخزون (المتطلب: {$quantity}، المتاح: {$currentQty})"
                        ], 400);
                    }
                }

                $subtotal = $product->selling_price * $quantity;
                $totalAmount += $subtotal;

                $itemsToInsert[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ];
            }

            // التحقق من المبلغ المدفوع
            if ($request->paid_amount < $totalAmount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'المبلغ المدفوع أقل من إجمالي الفاتورة.'
                ], 400);
            }

            // إنشاء الفاتورة
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'branch_id' => $mainBranch ? $mainBranch->id : null,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $request->paid_amount - $totalAmount,
                'payment_method' => $request->payment_method,
            ]);

            // حفظ تفاصيل الفاتورة وخصم الكمية من المخزون
            foreach ($itemsToInsert as $itemData) {
                $product = $itemData['product'];
                
                // حفظ عنصر الفاتورة
                $invoice->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                // تحديث كمية المخزون للفرع
                if ($mainBranch) {
                    $branchProduct = $product->branches->where('id', $mainBranch->id)->first();
                    $currentQty = $branchProduct ? $branchProduct->pivot->quantity : 0;
                    $newQty = $currentQty - $itemData['quantity'];

                    $product->branches()->updateExistingPivot($mainBranch->id, [
                        'quantity' => $newQty
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الفاتورة بنجاح!',
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الفاتورة: ' . $e->getMessage()
            ], 500);
        }
    }
}