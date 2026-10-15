@extends('layouts.app')

@section('content')
<div class="container-xl">
    <!-- عنوان الصفحة وزر الطباعة والإضافة -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title fw-bold">سجل الفواتير والمبيعات</h2>
        <div>
            <a href="{{ route('invoices.index', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-outline-primary me-2">
                🖨️ طباعة تقرير المبيعات
            </a>
            <a href="{{ route('pos.index') }}" class="btn btn-primary">
                + نقطة البيع (POS)
            </a>
        </div>
    </div>

    <!-- كروت الإحصائيات العلوية -->
    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي عدد الفواتير</div>
                    <div class="fw-bold fs-2 text-dark">{{ number_format($invoicesCount) }} <small class="fs-6">فاتورة</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm border-start border-primary border-3">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي قيمة المبيعات</div>
                    <div class="fw-bold fs-2 text-primary">{{ number_format($totalSalesSum, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">المبلغ المحصل الفعلي</div>
                    <div class="fw-bold fs-2 text-success">{{ number_format($totalPaidSum, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي الديون (الآجل)</div>
                    <div class="fw-bold fs-2 text-danger">{{ number_format($totalRemainingSum, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- بطاقة الفلاتر والبحث -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light">
            <form action="{{ route('invoices.index') }}" method="GET" class="row g-3 align-items-end">
                <!-- البحث -->
                <div class="col-md-4">
                    <label class="form-label">بحث برقم الفاتورة أو الزبون</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="ابحث هنا...">
                </div>

                <!-- طريقة الدفع -->
                <div class="col-md-3">
                    <label class="form-label">طريقة الدفع</label>
                    <select name="payment_method" class="form-select">
                        <option value="">جميع الطرق</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقداً (كاش)</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>بطاقة</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>آجل / دين</option>
                    </select>
                </div>

                <!-- من تاريخ -->
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>

                <!-- إلى تاريخ -->
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>

                <!-- أزرار البحث -->
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" title="بحث">بحث</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary" title="إعادة تعيين">✖</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول الفواتير -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>الزبون</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>طريقة الدفع</th>
                        <th>التاريخ</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="fw-bold text-primary">{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->customer->name ?? 'زبون نقدي' }}</td>
                            <td class="fw-bold">{{ number_format($inv->total_amount, 2) }} د.ل</td>
                            <td>{{ number_format($inv->paid_amount, 2) }} د.ل</td>
                            <td>
                                @if($inv->payment_method === 'cash')
                                    <span class="badge bg-success-lt text-success">نقداً</span>
                                @elseif($inv->payment_method === 'card')
                                    <span class="badge bg-info-lt text-info">بطاقة</span>
                                @else
                                    <span class="badge bg-warning-lt text-warning">آجل / دين</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('invoices.show', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">عرض وطباعة</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">لا توجد فواتير مطابقة للبحث.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- روابط التصفح (Pagination) -->
        <div class="card-footer d-flex align-items-center">
            {!! $invoices->links() !!}
        </div>
    </div>
</div>
@endsection