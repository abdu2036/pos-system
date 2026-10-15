@extends('layouts.app')

@section('title', 'تفاصيل الوردية')

@section('content')
<div class="container-fluid">
    <!-- الأزرار العلوية (تختفي عند الطباعة) -->
    <div class="row mb-4 d-print-none">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">📄 تفاصيل الوردية #{{ $shift->id }}</h2>
                <p class="text-muted">مراجعة تفاصيل حركة الصندوق، الفرع، وطرق الدفع والديون.</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> طباعة التقرير
                </button>
                <a href="{{ route('shifts.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-right"></i> عودة لقائمة الورديات
                </a>
            </div>
        </div>
    </div>

    <!-- رأس الفاتورة أو التقرير للطباعة -->
    <div class="text-center mb-4 d-none d-print-block">
        <h2 class="fw-bold">تقرير تفاصيل الوردية رقم (#{{ $shift->id }})</h2>
        <p class="text-muted">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
        <hr>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <label class="form-label text-muted">الفرع:</label>
                    <p class="fw-bold fs-5 text-primary">{{ $shift->branch->name ?? ($shift->user->branch->name ?? 'الفرع الرئيسي') }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label text-muted">اسم الكاشير:</label>
                    <p class="fw-bold fs-5">{{ $shift->user->name ?? 'غير متوفر' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label text-muted">حالة الوردية:</label>
                    <p>
                        @if($shift->status == 'open')
                            <span class="badge bg-success fs-6">🟢 مفتوحة</span>
                        @else
                            <span class="badge bg-secondary fs-6">🔴 مغلقة</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label text-muted">مبلغ الافتتاح:</label>
                    <p class="fw-bold fs-5">{{ number_format($shift->opening_balance, 2) }} د.ل</p>
                </div>

                <div class="col-md-4 col-6">
                    <label class="form-label text-muted">إجمالي المبيعات:</label>
                    <p class="fw-bold fs-5 text-success">{{ number_format($shift->total_sales ?? 0, 2) }} د.ل</p>
                </div>
                <div class="col-md-4 col-6">
                    <label class="form-label text-muted">مبيعات البطاقة / الصك:</label>
                    @php
                        $cardSalesShow = $shift->invoices ? $shift->invoices->whereIn('payment_method', ['card', 'check', 'بطاقة', 'صك'])->sum('total_amount') : 0;
                    @endphp
                    <p class="fw-bold fs-5 text-info">{{ number_format($cardSalesShow, 2) }} د.ل</p>
                </div>
                <div class="col-md-4 col-12">
                    <label class="form-label text-muted">المبلغ المتوقع في الدرج (نقدي):</label>
                    <p class="fw-bold fs-5 text-primary">{{ number_format($shift->expected_balance ?? ($shift->opening_balance + ($shift->total_sales ?? 0)), 2) }} د.ل</p>
                </div>

                <div class="col-md-6 col-6">
                    <label class="form-label text-muted">وقت فتح الوردية:</label>
                    <p class="fw-semibold">{{ $shift->opened_at ?? $shift->created_at }}</p>
                </div>
                <div class="col-md-6 col-6">
                    <label class="form-label text-muted">وقت إغلاق الوردية:</label>
                    <p class="fw-semibold">{{ $shift->closed_at ?? 'الوردية لم تغلق بعد' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول مبيعات الدين والزبائن خلال هذه الوردية -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0 fw-bold">👥 فواتير الدين والزبائن المرتبطين بهذه الوردية</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>اسم الزبون (المدين)</th>
                            <th>إجمالي الفاتورة</th>
                            <th>طريقة الدفع</th>
                            <th>الوقت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $creditInvoicesShow = $shift->invoices ? $shift->invoices->whereIn('payment_method', ['credit', 'آجل', 'دين', 'آجل / دين']) : collect();
                        @endphp
                        @forelse($creditInvoicesShow as $inv)
                        <tr>
                            <td class="fw-bold text-primary">{{ $inv->invoice_number }}</td>
                            <td class="fw-bold text-danger">{{ $inv->customer->name ?? 'زبون عام' }}</td>
                            <td>{{ number_format($inv->total_amount, 2) }} د.ل</td>
                            <td><span class="badge bg-warning text-dark">دين / آجل</span></td>
                            <td>{{ $inv->created_at }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center py-3">لا توجد مبيعات بالدين مسجلة في هذه الوردية.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- تنسيقات خاصة بالطباعة لإخفاء القوائم الجانبية والأزرار -->
<style>
@media print {
    body {
        background-color: #fff !important;
        color: #000 !important;
    }
    .sidebar, .navbar, footer, .d-print-none {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .container-fluid {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
@endsection