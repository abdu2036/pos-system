<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
   public function index()
    {
        $products = Product::with('branches')->get();
        $customers = Customer::all(); // جلب جميع الزبائن

        // جلب آخر 10 فواتير مع بيانات الزبون لعرضها في القائمة المنبثقة (F5)
        $recentInvoices = \App\Models\Invoice::with('customer')->latest()->take(10)->get();

        return view('admin.pos.index', compact('products', 'customers', 'recentInvoices'));
    }

public function store(Request $request)
{
    $request->validate([
        'customer_id' => 'nullable|exists:customers,id',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'paid_amount' => 'required|numeric|min:0',
        'payment_method' => 'required|string|in:cash,card,transfer,credit',
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

        // معالجة طريقة الدفع الآجل (credit)
        $isCredit = $request->payment_method === 'credit';
        
        // في حالة الآجل، إذا كان المبلغ المدفوع المرسل مساوياً للإجمالي يُعتبر المدفوع 0 (أو المبلغ المقدم المكتوب)
        $actualPaidAmount = $isCredit 
            ? ($request->paid_amount >= $totalAmount ? 0 : $request->paid_amount) 
            : $request->paid_amount;

        // حساب المتبقي (الديون)
        $remainingAmount = $totalAmount - $actualPaidAmount;

        // التحقق عند البيع بالآجل
        if ($isCredit) {
            if (!$request->customer_id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'يجب اختيار زبون مسجل لتسجيل الفاتورة كدين/آجل.'
                ], 400);
            }

            // **فحص الحد الائتماني للزبون**
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                $expectedBalance = $customer->balance + $remainingAmount;

                // إذا كان للزبون حد ائتماني أكبر من 0 والدين المتوقع يتجاوزه
                if ($customer->credit_limit > 0 && $expectedBalance > $customer->credit_limit) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'تعذر إتمام العملية: الدين المتوقع بعد هذه الفاتورة (' . number_format($expectedBalance, 2) . ' د.ل) يتجاوز الحد الائتماني المسموح للزبون (' . number_format($customer->credit_limit, 2) . ' د.ل).'
                    ], 400);
                }
            }
        }

        // إنشاء الفاتورة
        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'customer_id' => $request->customer_id ?? null,
            'branch_id' => $mainBranch ? $mainBranch->id : null,
            'total_amount' => $totalAmount,
            'paid_amount' => $actualPaidAmount,
            'change_amount' => !$isCredit && $actualPaidAmount > $totalAmount ? ($actualPaidAmount - $totalAmount) : 0,
            'payment_method' => $request->payment_method,
        ]);

        // إذا كانت الفاتورة آجلة وهناك متبقي على الزبون
        if ($remainingAmount > 0 && $request->customer_id) {
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                // 1. زيادة رصيد الدين على الزبون
                $customer->increment('balance', $remainingAmount);

                // 2. تسجيل حركة مالية في جدول transactions مع ربط invoice_id مباشرة
                $customer->transactions()->create([
                    'invoice_id' => $invoice->id,
                    'type' => 'invoice',
                    'amount' => $remainingAmount,
                    'description' => 'فاتورة مبيعات آجلة رقم: ' . $invoice->invoice_number,
                    'payment_method' => $request->payment_method,
                ]);
            }
        }

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