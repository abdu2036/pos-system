<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanySetting; // أو نموذج إعدادات الشركة لديك

class PosController extends Controller
{
    public function index()
    {
        $products = Product::with('branches')->get();
        $customers = Customer::all();
        $recentInvoices = Invoice::with('customer')->latest()->take(10)->get();

        $company = \App\Models\CompanySetting::first();
        $layout = $company->pos_layout ?? 'classic';

        $viewPath = "admin.pos.layouts.{$layout}";
        if (!view()->exists($viewPath)) {
            $viewPath = "admin.pos.layouts.classic";
        }

        $treasuries = Treasury::where('is_active', true)->get();

        return view($viewPath, compact('products', 'customers', 'recentInvoices', 'treasuries'));
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
            'treasury_id' => 'nullable|exists:treasuries,id',
        ]);

        $mainBranch = Branch::first();

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $itemsToInsert = [];

            $productIds = collect($request->items)->pluck('product_id');
            $products = Product::with('branches')->whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = $item['quantity'];

                if (!$product) {
                    throw new \Exception("المنتج المطلوب غير موجود.");
                }

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

            $isCredit = $request->payment_method === 'credit';
            $actualPaidAmount = $isCredit 
                ? ($request->paid_amount >= $totalAmount ? 0 : $request->paid_amount) 
                : $request->paid_amount;

            $remainingAmount = $totalAmount - $actualPaidAmount;

            if ($isCredit) {
                if (!$request->customer_id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'يجب اختيار زبون مسجل لتسجيل الفاتورة كدين/آجل.'
                    ], 400);
                }

                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    $expectedBalance = $customer->balance + $remainingAmount;
                    if ($customer->credit_limit > 0 && $expectedBalance > $customer->credit_limit) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'تعذر إتمام العملية: الدين المتوقع يتجاوز الحد الائتماني للزبون.'
                        ], 400);
                    }
                }
            }

            $year = date('Y');
            $latestInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}-%")->latest('id')->first();
            $nextInvNumber = $latestInvoice ? intval(substr($latestInvoice->invoice_number, -4)) + 1 : 1;
            $invoiceNumber = 'INV-' . $year . '-' . str_pad($nextInvNumber, 4, '0', STR_PAD_LEFT);

            // 🕒 1. التحقق من إعدادات الشركة ونظام الورديات
            $companySetting = CompanySetting::first();
            // تحقق من حقل تففعيل الورديات (تأكد من مطابقة اسم العمود في جدول company_settings لديك، مثل shifts_enabled أو enable_shifts)
            $shiftsEnabled = $companySetting && ($companySetting->shifts_enabled ?? false); 

            $activeShiftId = null;

            if ($shiftsEnabled) {
                $activeShift = \App\Models\Shift::where('user_id', Auth::id())
                    ->where('status', 'open')
                    ->first();

                if (!$activeShift) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'عذراً، نظام الورديات مفعل ولا توجد وردية مفتوحة حالياً لك. يرجى فتح وردية جديدة قبل إتمام البيع.'
                    ], 400);
                }
                
                $activeShiftId = $activeShift->id;
            }

            // 📝 2. إنشاء الفاتورة مع مراعاة حالة نظام الورديات (مفعل أو معطل)
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $request->customer_id ?? null,
                'branch_id' => $mainBranch ? $mainBranch->id : null,
                'shift_id' => $activeShiftId, // 👈 سيكون رقم الوردية إذا كانت مفعلة، أو null إذا كان النظام عادياً بدون ورديات
                'total_amount' => $totalAmount,
                'paid_amount' => $actualPaidAmount,
                'change_amount' => !$isCredit && $actualPaidAmount > $totalAmount ? ($actualPaidAmount - $totalAmount) : 0,
                'payment_method' => $request->payment_method,
            ]);
            
            // 💰 التوجيه الذكي للخزينة حسب طريقة الدفع
            if ($actualPaidAmount > 0) {
                $treasuryId = $request->treasury_id;
                
                if (!$treasuryId) {
                    if (in_array($request->payment_method, ['card', 'transfer'])) {
                        $targetTreasury = Treasury::where('type', 'bank')->first() ?? Treasury::first();
                    } else {
                        $targetTreasury = Treasury::where('type', 'cash')->first() ?? Treasury::first();
                    }
                    $treasuryId = $targetTreasury ? $targetTreasury->id : null;
                }

                if ($treasuryId) {
                    $treasury = Treasury::findOrFail($treasuryId);

                    TreasuryTransaction::create([
                        'treasury_id' => $treasury->id,
                        'type' => 'deposit',
                        'amount' => $actualPaidAmount,
                        'source_type' => 'sale',
                        'source_id' => $invoice->id,
                        'user_id' => Auth::id(),
                        'notes' => 'إيراد مبيعات فاتورة (' . $request->payment_method . ') رقم: ' . $invoice->invoice_number,
                    ]);

                    $treasury->current_balance += $actualPaidAmount;
                    $treasury->save();
                }
            }

            if ($remainingAmount > 0 && $request->customer_id) {
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    $customer->increment('balance', $remainingAmount);
                    $customer->transactions()->create([
                        'invoice_id' => $invoice->id,
                        'type' => 'invoice',
                        'amount' => $remainingAmount,
                        'description' => 'فاتورة مبيعات آجلة رقم: ' . $invoice->invoice_number,
                        'payment_method' => $request->payment_method,
                    ]);
                }
            }

            foreach ($itemsToInsert as $itemData) {
                $product = $itemData['product'];
                
                $invoice->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['subtotal'],
                ]);

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
                'message' => 'تم حفظ الفاتورة وتوجيه المبلغ للخزينة بنجاح!',
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