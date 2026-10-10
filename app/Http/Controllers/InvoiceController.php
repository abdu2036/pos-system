<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        // جلب الفواتير مرتبة من الأحدث للأقدم مع علاقات الزبون والفرع والعناصر
        $invoices = Invoice::with(['customer', 'branch', 'items.product'])
            ->latest()
            ->paginate(15);

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'branch', 'items.product'])->findOrFail($id);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function print($id)
    {
        // جلب الفاتورة المحددة فقط
        $invoice = Invoice::with(['customer', 'branch', 'items.product'])->findOrFail($id);
        $company = CompanySetting::first();

        return view('admin.invoices.print', compact('invoice', 'company'));
    }

    /**
     * إلغاء/حذف الفاتورة وإعادة المخزون وتعديل حساب الزبون
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $invoice = Invoice::with(['items', 'customer', 'branch'])->findOrFail($id);

            // 1. إعادة الكميات المباعة إلى مخزون الفرع
            if ($invoice->branch_id) {
                foreach ($invoice->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $branchProduct = $product->branches->where('id', $invoice->branch_id)->first();
                        $currentQty = $branchProduct ? $branchProduct->pivot->quantity : 0;

                        // إرجاع الكمية إلى المخزون
                        $product->branches()->updateExistingPivot($invoice->branch_id, [
                            'quantity' => $currentQty + $item->quantity
                        ]);
                    }
                }
            }

            // 2. إذا كانت الفاتورة مرتبطة بزبون وعليهم آجل/ديون، يتم خصم قيمة الدين المنشأة بهذه الفاتورة
            if ($invoice->customer_id) {
                $remainingAmount = $invoice->total_amount - $invoice->paid_amount;

                // إذا كان هناك دين متبقي ثبت على الزبون من هذه الفاتورة
                if ($remainingAmount > 0 && $invoice->customer) {
                    $invoice->customer->decrement('balance', $remainingAmount);
                }

                // حذف جميع المعاملات المالية المربوطة بهذه الفاتورة في جدول transactions
                $invoice->customer->transactions()->where('invoice_id', $invoice->id)->delete();
            }

            // 3. حذف عناصر الفاتورة ثم الفاتورة نفسها
            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();

            return redirect()->route('invoices.index')->with('success', 'تم إلغاء وحذف الفاتورة وإعادة الكميات للمخزون وتسوية الحسابات بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء إلغاء الفاتورة: ' . $e->getMessage());
        }
    }
}