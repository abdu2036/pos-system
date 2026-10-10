@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">تفاصيل المورد: {{ $supplier->name }}</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                    رجوع لقائمة الموردين
                </a>
            </div>
        </div>
    </div>

    {{-- بطاقة بيانات المورد الأساسية --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">البيانات الشخصية والاتصال</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>اسم المورد:</strong> {{ $supplier->name }}
                </div>
                <div class="col-md-4">
                    <strong>رقم الهاتف:</strong> {{ $supplier->phone ?? 'غير محدد' }}
                </div>
                <div class="col-md-4">
                    <strong>البريد الإلكتروني:</strong> {{ $supplier->email ?? 'غير محدد' }}
                </div>
                <div class="col-md-12">
                    <strong>العنوان:</strong> {{ $supplier->address ?? 'غير محدد' }}
                </div>
            </div>
        </div>
    </div>

    {{-- سجل فواتير الشراء --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">سجل فواتير الشراء</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>طريقة الدفع</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplier->purchaseInvoices ?? [] as $invoice)
                        <tr>
                            <td>
                                <span class="badge bg-blue-lt fs-6">{{ $invoice->invoice_number }}</span>
                            </td>
                            <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @php $method = strtolower(trim($invoice->payment_method)); @endphp
                                @if($method == 'credit' || $method == 'آجل' || $method == 'آجل / دين')
                                    <span class="badge bg-warning text-warning-fg">آجل / دين</span>
                                @elseif($method == 'cash' || $method == 'نقداً')
                                    <span class="badge bg-success text-success-fg">نقداً (كاش)</span>
                                @else
                                    <span class="badge bg-info text-info-fg">تحويل مصرفي</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ number_format($invoice->total_amount, 2) }} د.ل</td>
                            <td class="text-success">{{ number_format($invoice->paid_amount, 2) }} د.ل</td>
                            <td class="text-danger fw-bold">
                                {{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }} د.ل
                            </td>
                            <td>
                                <a href="{{ route('purchase_invoices.edit', $invoice->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                    تعديل
                                </a>
                                {{-- زر طباعة الفاتورة --}}
                                <a href="{{ route('purchase_invoices.print', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    طباعة الفاتورة
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">لا توجد فواتير شراء مسجلة لهذا المورد.</td>
                        </tr>
                    
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- سجل السداد والمعاملات المالية الأخرى --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">سجل الدفعات والمعاملات المالية</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>نوع المعاملة / البيان</th>
                        <th>طريقة الدفع</th>
                        <th>المبلغ</th>
                        <th>ملاحظات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if(($transaction->type ?? '') == 'payment' || ($transaction->type ?? '') == 'سداد' || ($transaction->type ?? '') == 'دفعة للمورد')
                                    <span class="badge bg-green-lt">دفعة للمورد</span>
                                @else
                                    <span class="badge bg-secondary-lt">{{ $transaction->type }}</span>
                                @endif
                            </td>
                            <td>
                                @php $pMethod = strtolower(trim($transaction->payment_method ?? '')); @endphp
                                @if($pMethod == 'bank_transfer' || $pMethod == 'transfer' || $pMethod == 'تحويل بنكي' || $pMethod == 'تحويل مصرفي')
                                    <span class="badge bg-info text-info-fg">تحويل بنكي</span>
                                @elseif($pMethod == 'cash' || $pMethod == 'نقداً')
                                    <span class="badge bg-success text-success-fg">نقداً (كاش)</span>
                                @elseif($pMethod != '')
                                    <span class="badge bg-secondary text-secondary-fg">{{ $transaction->payment_method }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-bold text-success">{{ number_format($transaction->amount ?? 0, 2) }} د.ل</td>
                            <td>{{ $transaction->notes ?? '-' }}</td>
                            <td>
                                {{-- زر طباعة الواصل المالي للمورد --}}
                                <a href="{{ route('supplier_payments.print', $transaction->id) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    طباعة الواصل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">لا توجد دفعات مسجلة لهذا المورد حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection