<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\CompanySetting;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'branch', 'items.product']);

        // 1. البحث برقم الفاتورة أو اسم الزبون
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. فلترة حسب طريقة الدفع
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        // 3. فلترة حسب التاريخ (من - إلى)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // حساب الإجماليات والكروت بناءً على النتائج المفلترة
        $statsQuery = clone $query;
        $totalSalesSum = $statsQuery->sum('total_amount');
        $totalPaidSum = $statsQuery->sum('paid_amount');
        $totalRemainingSum = $totalSalesSum - $totalPaidSum;
        $invoicesCount = $statsQuery->count();

        // جلب الفواتير مرتبة من الأحدث للأقدم مع التصفح
        $invoices = $query->latest()->paginate(15)->withQueryString();

        // فحص إذا كان المطلوب طباعة التقرير مباشرة
        if ($request->has('print')) {
            $company = \App\Models\CompanySetting::first();
            return view('admin.invoices.print_report', compact('invoices', 'totalSalesSum', 'totalPaidSum', 'totalRemainingSum', 'invoicesCount', 'company'));
        }

        return view('admin.invoices.index', compact(
            'invoices', 
            'totalSalesSum', 
            'totalPaidSum', 
            'totalRemainingSum', 
            'invoicesCount'
        ));
    }

    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'branch', 'items.product'])->findOrFail($id);
        $company = CompanySetting::first();

        return view('admin.invoices.show', compact('invoice', 'company'));
    }

    public function print($id)
    {
        $invoice = Invoice::with(['customer', 'branch', 'items.product'])->findOrFail($id);
        $company = CompanySetting::first();

        return view('admin.invoices.print', compact('invoice', 'company'));
    }

    /**
     * إلغاء/حذف الفاتورة وإعادة المخزون وعكس الحركة المالية من الخزينة وتعديل حساب الزبون
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

            // 2. عكس الحركة المالية من الخزينة أو البنك
            $transaction = TreasuryTransaction::where('source_type', 'sale')
                ->where('source_id', $invoice->id)
                ->first();

            if ($transaction) {
                $treasury = Treasury::find($transaction->treasury_id);
                if ($treasury) {
                    // خصم القيمة المسددة من الرصيد الحالي للخزينة/المصرف
                    $treasury->current_balance -= $transaction->amount;
                    $treasury->save();
                }
                // حذف سجل الحركة المالية
                $transaction->delete();
            }

            // 3. إذا كانت الفاتورة مرتبطة بزبون وعليه آجل/ديون، يتم خصم قيمة الدين المنشأة بهذه الفاتورة
            if ($invoice->customer_id) {
                $remainingAmount = $invoice->total_amount - $invoice->paid_amount;

                if ($remainingAmount > 0 && $invoice->customer) {
                    $invoice->customer->decrement('balance', $remainingAmount);
                }

                // حذف المعاملات المالية المربوطة بهذه الفاتورة في جدول معاملات الزبون
                $invoice->customer->transactions()->where('invoice_id', $invoice->id)->delete();
            }

            // 4. حذف عناصر الفاتورة ثم الفاتورة نفسها
            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();

            return redirect()->route('invoices.index')->with('success', 'تم إلغاء وحذف الفاتورة وعكس الحركة المالية من الخزينة وإعادة المخزون بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء إلغاء الفاتورة: ' . $e->getMessage());
        }
    }
}