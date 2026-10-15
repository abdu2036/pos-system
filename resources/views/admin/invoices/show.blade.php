@extends('layouts.app')

@section('content')
<div class="container-xl">
    <!-- هيدر الصفحة وأزرار الإجراءات (تختفي عند الطباعة) -->
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    تفاصيل الفاتورة #{{ $invoice->invoice_number }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button type="button" class="btn btn-primary" onclick="javascript:window.print();">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                    طباعة الفاتورة
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary ms-2">
                    رجوع لسجل الفواتير
                </a>
            </div>
        </div>
    </div>

    <!-- بطاقة الفاتورة -->
    <div class="card card-lg print-card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <p class="h3">{{ $company->name ?? 'منظومة المبيعات' }}</p>
                    <address>
                        {{ $invoice->branch->name ?? 'الفرع الرئيسي' }}<br>
                        التاريخ: {{ $invoice->created_at->format('Y-m-d H:i') }}<br>
                        طريقة الدفع: 
                        <span class="badge {{ $invoice->payment_method == 'cash' ? 'bg-success text-white' : ($invoice->payment_method == 'card' ? 'bg-info text-white' : 'bg-warning text-dark') }}">
                            {{ $invoice->payment_method == 'cash' ? 'نقداً' : ($invoice->payment_method == 'card' ? 'بطاقة' : ($invoice->payment_method == 'transfer' ? 'تحويل' : 'آجل / دين')) }}
                        </span>
                    </address>
                </div>
                <div class="col-6 text-end">
                    <p class="h3">بيانات الزبون</p>
                    <address>
                        <strong>{{ $invoice->customer ? $invoice->customer->name : 'زبون نقدي (عام)' }}</strong><br>
                        @if($invoice->customer && $invoice->customer->phone)
                            رقم الهاتف: {{ $invoice->customer->phone }}<br>
                        @endif
                        رقم الفاتورة: #{{ $invoice->invoice_number }}
                    </address>
                </div>
            </div>

            <!-- جدول الأصناف -->
            <table class="table table-transparent table-responsive my-4">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 1%">#</th>
                        <th>المنتج</th>
                        <th class="text-center" style="width: 10%">الكمية</th>
                        <th class="text-end" style="width: 15%">سعر الوحدة</th>
                        <th class="text-end" style="width: 15%">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <p class="strong mb-1">{{ $item->product->name ?? 'منتج غير محدد' }}</p>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }} د.ل</td>
                            <td class="text-end">{{ number_format($item->subtotal, 2) }} د.ل</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="strong text-end">الإجمالي الكلي</td>
                        <td class="text-end strong fs-3 text-primary">{{ number_format($invoice->total_amount, 2) }} د.ل</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="strong text-end">المبلغ المدفوع</td>
                        <td class="text-end">{{ number_format($invoice->paid_amount, 2) }} د.ل</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="strong text-end">المتبقي </td>
                        <td class="text-end">{{ number_format($invoice->change_amount, 2) }} د.ل</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- تنسيقات مخصصة للطباعة لإخفاء القوائم الجانبية والهيدر -->
<style>
@media print {
    /* إخفاء القائمة الجانبية، الهيدر العام، وأزرار الصفحة */
    .navbar, .navbar-vertical, .page-header, footer, .d-print-none {
        display: none !important;
    }
    body {
        background-color: #fff !important;
        color: #000 !important;
        margin: 0;
        padding: 0;
    }
    .container-xl {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
@endsection