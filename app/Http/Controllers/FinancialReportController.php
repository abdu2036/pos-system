<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Invoice;         
use App\Models\PurchaseInvoice; 
use App\Models\Transaction;

class FinancialReportController extends Controller
{
    public function index()
    {
        // 1. حساب المخزون والأرباح المتوقعة من المنتجات
        $products = Product::with(['branches'])->get();

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

        $expectedRetailProfit = $totalRetailSales - $totalPurchaseCost;
        $expectedWholesaleProfit = $totalWholesaleSales - $totalPurchaseCost;

        // 2. إجمالي المبيعات والمشتريات العامة
        $totalSalesAmount = Invoice::sum('total_amount') ?? 0;
        $totalPurchasesAmount = PurchaseInvoice::sum('total_amount') ?? 0;

        // 3. صافي الأرباح
        $netProfit = $totalSalesAmount > 0 ? ($totalSalesAmount - $totalPurchasesAmount) : $expectedRetailProfit;

        // 4. حساب السيولة النقدية الفعليّة للخزينة (وارد - صادر) بدون استخدام أعمدة غير موجودة
        $salesPaid = Invoice::sum('paid_amount') ?? 0;
        
        // جلب العمليات المالية من جدول transactions مباشرة
        $totalTransactionsAmount = Transaction::sum('amount') ?? 0;
        
        // المبيعات المدفوعة مضافاً إليها المعاملات (إذا كانت سندات قبض أو دفع)
        // يمكنك تعديل التصفية حسب نوع المعاملة المسجلة لديك في النظام
        $purchasesPaid = PurchaseInvoice::sum('paid_amount') ?? 0;
        
        // صافي الخزينة = إجمالي الإيرادات المدفوعة ناقص إجمالي المشتريات المدفوعة والمدفوعات الأخرى
        $totalLiquidity = $salesPaid - $purchasesPaid;

        return view('admin.reports.financial', compact(
            'totalQuantity',
            'totalPurchaseCost',
            'totalRetailSales',
            'totalWholesaleSales',
            'expectedRetailProfit',
            'expectedWholesaleProfit',
            'totalSalesAmount',
            'totalPurchasesAmount',
            'netProfit',
            'totalLiquidity'
        ));
    }
}