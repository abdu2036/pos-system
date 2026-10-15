@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title fw-bold">سجل فواتير الشراء (المشتريات)</h2>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                @can('print report invoices')
                <a href="{{ route('purchase_invoices.index', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-outline-primary">
                    🖨️ طباعة تقرير المشتريات
                </a>
                @endcan
                @can('create purchase invoices')
                <a href="{{ route('purchase_invoices.create') }}" class="btn btn-primary">
                    + إضافة فاتورة شراء جديدة
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- كروت الإحصائيات العلوية -->
    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي الفواتير</div>
                    <div class="fw-bold fs-2 text-dark">{{ number_format($totalInvoices ?? 0) }} <small class="fs-6">فاتورة</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي المشتريات</div>
                    <div class="fw-bold fs-2 text-primary">{{ number_format($totalAmount ?? 0, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي المدفوع</div>
                    <div class="fw-bold fs-2 text-success">{{ number_format($totalPaid ?? 0, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm border-start border-danger border-3">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي المتبقي (الديون)</div>
                    <div class="fw-bold fs-2 text-danger">{{ number_format($totalRemaining ?? 0, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
    </div>

  <!-- شريط البحث والفلترة حسب التاريخ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-light">
        <form action="{{ route('purchase_invoices.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label mb-1">بحث برقم الفاتورة أو المورد</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="ابحث هنا...">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">من تاريخ</label>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2 d-flex gap-1 align-items-end">
                <button type="submit" class="btn btn-primary w-100">تصفية</button>
                <a href="{{ route('purchase_invoices.index') }}" class="btn btn-outline-secondary" title="إعادة تعيين">✖</a>
            </div>
        </form>
    </div>
</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap datatable">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>المورد</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>طريقة الدفع</th>
                        <th>التاريخ</th>
                        <th class="text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        @php 
                            $method = strtolower(trim($invoice->payment_method));
                            $remaining = $invoice->total_amount - $invoice->paid_amount;
                        @endphp
                        <tr>
                            <td><span class="badge bg-blue text-white fw-bold">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->supplier ? $invoice->supplier->name : 'غير محدد' }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }} د.ل</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }} د.ل</td>
                            <td>
                                @if($remaining > 0)
                                    <span class="text-danger fw-bold">{{ number_format($remaining, 2) }} د.ل</span>
                                @else
                                    <span class="text-success fw-bold">0.00 د.ل</span>
                                @endif
                            </td>
                            <td>
                                @if($method == 'cash' || $method == 'نقداً' || $method == 'نقداً (كاش)')
                                    <span class="badge bg-success text-white">نقداً</span>
                                @elseif($method == 'bank_transfer' || $method == 'bank' || $method == 'transfer' || $method == 'تحويل مصرفي')
                                    <span class="badge bg-info text-white">تحويل مصرفي</span>
                                @elseif($method == 'credit' || $method == 'آجل' || $method == 'آجل / دين')
                                    <span class="badge bg-warning text-dark">آجل / دين</span>
                                @else
                                    <span class="badge bg-secondary text-white">{{ $invoice->payment_method }}</span>
                                @endif
                            </td>
                            <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <div class="btn-list flex-nowrap justify-content-end">
                                    @if($remaining > 0)
                                    @can('settle purchase invoices')
                                        
                                        
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                            data-bs-target="#settleModal{{ $invoice->id }}">
                                            تسوية الحساب
                                        </button>
                                    @endcan
                                    @endif
                                    @can('edit purchase invoices')
                                    <a href="{{ route('purchase_invoices.edit', $invoice->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        تعديل
                                    </a>
                                    @endcan
                                    @can('print purchase invoices')
                                    <a href="{{ route('purchase_invoices.print', $invoice->id) }}" target="_blank"
                                        class="btn btn-sm btn-info text-white">طباعة</a>
                                    @endcan
                                    @can('delete purchase invoices')
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $invoice->id }}">
                                        حذف
                                    </button>
                                    @endcan
                                </div>

                                {{-- Modal تسوية الحساب --}}
                                @if($remaining > 0)
                                    <div class="modal fade" id="settleModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog text-start">
                                            <div class="modal-content">
                                                <form action="{{ route('purchase_invoices.settle', $invoice->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تسوية حساب الفاتورة ({{ $invoice->invoice_number }})</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">المبلغ المتبقي</label>
                                                            <input type="text" class="form-control"
                                                                value="{{ number_format($remaining, 2) }} د.ل" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">المبلغ المدفوع الآن <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" name="amount" class="form-control"
                                                                max="{{ $remaining }}" value="{{ $remaining }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">طريقة السداد <span class="text-danger">*</span></label>
                                                            <select name="payment_method" class="form-select" required>
                                                                <option value="cash">نقداً (كاش)</option>
                                                                <option value="bank_transfer">تحويل مصرفي</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" class="btn btn-success">تأكيد التسوية</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Modal تأكيد الحذف --}}
                                <div class="modal fade" id="deleteModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog text-start">
                                        <div class="modal-content">
                                            <form action="{{ route('purchase_invoices.destroy', $invoice->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">حذف فاتورة شراء</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    هل أنت تأكد من رغبتك في حذف الفاتورة رقم <strong>{{ $invoice->invoice_number }}</strong>؟
                                                    <p class="text-danger mt-2 mb-0"><small>* تنبيه: قد يتسبب الحذف في تعديل كميات المخزون المرتبطة بهذه الفاتورة.</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    <button type="submit" class="btn btn-danger">تأكيد الحذف</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">لا توجد فواتير شراء مسجلة حتى الآن.</td>
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