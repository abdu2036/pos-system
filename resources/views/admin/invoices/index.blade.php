@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">سجل الفواتير والمبيعات</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap datatable">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>الزبون</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>طريقة الدفع</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><span class="badge bg-blue text-white fw-bold">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->customer ? $invoice->customer->name : 'زبون نقدي' }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }} د.ل</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }} د.ل</td>
                            <td>
                                @php
                                    $method = strtolower(trim($invoice->payment_method ?? ''));
                                @endphp

                                @if($method == 'cash')
                                    <span class="badge bg-success text-white">نقداً</span>
                                @elseif($method == 'card')
                                    <span class="badge bg-info text-white">بطاقة</span>
                                @elseif($method == 'transfer')
                                    <span class="badge bg-primary text-white">تحويل</span>
                                @elseif($method == 'credit' || $method == 'debt')
                                    <span class="badge bg-warning text-dark">آجل / دين</span>
                                @else
                                    <span class="badge bg-secondary">{{ $invoice->payment_method ?? 'غير محدد' }}</span>
                                @endif
                            </td>
                            <td>{{ $invoice->created_format ?? $invoice->created_at->format('Y-m-d H:i') }}</td>
                          <td class="text-end">
    <!-- زر عرض وطباعة الفاتورة -->
    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary me-1" target="_blank">
        عرض وطباعة
    </a>

    <!-- زر حذف / إلغاء الفاتورة -->
    <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت تأكد من إلغاء وحذف هذه الفاتورة؟ سيتم إرجاع الكميات للمخزون.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger">
            حذف / إلغاء
        </button>
    </form>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">لا توجد فواتير مسجلة حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection