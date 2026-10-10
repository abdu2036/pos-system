@extends('layouts.app')

@section('content')
<style>
    @media print {
        .navbar, 
        .sidebar, 
        aside, 
        header, 
        footer, 
        .btn, 
        .no-print,
        .alert,
        th:last-child, 
        td:last-child {
            display: none !important;
        }

        body {
            background-color: #fff !important;
            color: #000 !important;
            font-size: 12pt;
            margin: 0;
            padding: 0;
        }

        .container, .container-xl {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .card {
            border: 1px solid #ccc !important;
            box-shadow: none !important;
            margin-bottom: 15px !important;
        }

        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .table th, .table td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
        }

        .badge {
            border: 1px solid #000 !important;
            color: #000 !important;
            background: transparent !important;
        }
    }
</style>

<div class="container-xl">
    <!-- تنبيهات النجاح والخطأ -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible no-print mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible no-print mb-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-1">كشف حساب الزبون: {{ $customer->name }}</h2>
            <div class="text-muted fs-5">رقم الهاتف: {{ $customer->phone ?? 'غير محدد' }}</div>
        </div>
        <div class="no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                طباعة كشف الحساب
            </button>
        </div>
    </div>

    <!-- بطاقات الملخص المالي للزبون -->
    <div class="row g-3 mb-3 justify-content-end">
        @php
            $totalPaid = $transactions->whereIn('type', ['payment', 'receipt'])->sum('amount');
        @endphp

        <!-- بطاقة إجمالي المدفوعات -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success-lt">
                <div class="card-body text-center p-3">
                    <div class="text-muted fs-6 mb-1">إجمالي المدفوع (المسدد)</div>
                    <div class="fs-1 fw-bold text-success">{{ number_format($totalPaid, 2) }} د.ل</div>
                </div>
            </div>
        </div>

        <!-- بطاقة الرصيد المتبقي (الدين) -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-danger-lt">
                <div class="card-body text-center p-3">
                    <div class="text-muted fs-6 mb-1">الرصيد المتبقي (الدين)</div>
                    <div class="fs-1 fw-bold text-danger">{{ number_format($customer->balance ?? 0, 2) }} د.ل</div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول المعاملات والمدفوعات -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h3 class="card-title fw-bold m-0">سجل المعاملات والمدفوعات</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter align-middle card-table table-hover">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>نوع الحركة</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>ملاحظات</th>
                        <th class="text-end pe-4 no-print">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($transaction->type == 'invoice' || $transaction->type == 'sale')
                                    <span class="badge bg-warning text-dark fs-6 px-2 py-1">فاتورة مبيعات</span>
                                @elseif($transaction->type == 'payment' || $transaction->type == 'receipt')
                                    <span class="badge bg-success text-white fs-6 px-2 py-1">سداد / قبض</span>
                                @else
                                    <span class="badge bg-secondary text-white fs-6 px-2 py-1">{{ $transaction->type }}</span>
                                @endif
                            </td>
                            <td class="fw-bold {{ ($transaction->type == 'payment' || $transaction->type == 'receipt') ? 'text-success' : 'text-danger' }}">
                                {{ number_format($transaction->amount, 2) }} د.ل
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fs-6">
                                    {{ $transaction->payment_method ?? 'cash' }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $transaction->notes ?? $transaction->description ?? '-' }}</td>
                            <td class="text-end pe-3 no-print">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @if($transaction->type == 'payment' || $transaction->type == 'receipt')
                                        <!-- طباعة الواصل -->
                                        <a href="{{ route('customers.receipt.print', $transaction->id) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                            طباعة واصل
                                        </a>

                                        <!-- إلغاء/حذف الواصل الخاطئ -->
                                        <form action="{{ route('customers.transactions.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الواصل؟ سيتم تعديل رصيد الزبون تلقائياً.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف الواصل">
                                                ✕
                                            </button>
                                        </form>
                                    @elseif($transaction->type == 'invoice' || $transaction->type == 'sale')
                                        @php
                                            $invoiceId = $transaction->invoice_id;
                                            if (!$invoiceId) {
                                                $invoiceId = \App\Models\Invoice::where('customer_id', $customer->id)
                                                    ->where('total_amount', $transaction->amount)
                                                    ->latest()
                                                    ->value('id');
                                            }
                                        @endphp

                                        @if($invoiceId)
                                            <a href="{{ route('invoices.print', $invoiceId) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                طباعة الفاتورة
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">لا توجد معاملات مسجلة لهذا الزبون.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection