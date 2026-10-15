@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title fw-bold">تقرير الحركة المالية والخزائن</h2>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                <a href="{{ route('treasuries.report', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-outline-primary">
                    🖨️ طباعة التقرير المالي
                </a>
            </div>
        </div>
    </div>

    <!-- كروت الإحصائيات العلوية -->
    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي السيولة الحالية</div>
                    <div class="fw-bold fs-2 text-dark">{{ number_format($totalTreasuryBalance, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي الإيداعات (+)</div>
                    <div class="fw-bold fs-2 text-success">{{ number_format($totalDeposits, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي السحب (-)</div>
                    <div class="fw-bold fs-2 text-danger">{{ number_format($totalWithdrawals, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm border-start border-primary border-3">
                <div class="card-body">
                    <div class="text-muted fs-6">صافي التدفق النقدي</div>
                    <div class="fw-bold fs-2 text-primary">{{ number_format($netCashFlow, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- شريط الفلترة والبحث -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light">
            <form action="{{ route('treasuries.report') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label mb-1">الخزينة / الحساب</label>
                    <select name="treasury_id" class="form-select">
                        <option value="">-- كل الخزائن والمصارف --</option>
                        @foreach($treasuries as $t)
                            <option value="{{ $t->id }}" {{ request('treasury_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">نوع الحركة</label>
                    <select name="type" class="form-select">
                        <option value="">-- الكل --</option>
                        <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>إيداع</option>
                        <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>سحب</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">من تاريخ</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">إلى تاريخ</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-1 align-items-end">
                    <button type="submit" class="btn btn-primary w-100">تصفية</button>
                    <a href="{{ route('treasuries.report') }}" class="btn btn-outline-secondary" title="إعادة تعيين">✖</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول الحركات -->
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap">
                <thead>
                    <tr>
                        <th>رقم الحركة</th>
                        <th>الخزينة / الحساب</th>
                        <th>نوع الحركة</th>
                        <th>المبلغ</th>
                        <th>مصدر العملية</th>
                        <th>المستخدم</th>
                        <th>التاريخ والوقت</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        @php
                            $sourceLabels = [
                                'supplier_payment' => 'سداد دفعة لمورد',
                                'customer_payment' => 'تحصيل دفعة من زبون',
                                'sale' => 'مبيعات نقطة البيع',
                                'purchase' => 'فاتورة شراء',
                                'opening_balance' => 'رصيد أول المدة',
                                'expense' => 'مصروفات'
                            ];
                            $sourceKey = strtolower(trim($trx->source_type));
                        @endphp
                        <tr>
                            <td><span class="badge bg-blue text-white">TRX-{{ $trx->id }}</span></td>
                            <td class="fw-bold">{{ $trx->treasury ? $trx->treasury->name : 'حذفت' }}</td>
                            <td>
                                @if($trx->type == 'deposit')
                                    <span class="badge bg-success text-white">إيداع (+D)</span>
                                @else
                                    <span class="badge bg-danger text-white">سحب (-W)</span>
                                @endif
                            </td>
                            <td class="fw-bold {{ $trx->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                {{ number_format($trx->amount, 2) }} د.ل
                            </td>
                            <td>
                                <span class="badge bg-secondary text-white">
                                    {{ $sourceLabels[$sourceKey] ?? $trx->source_type }}
                                </span>
                            </td>
                            <td>{{ $trx->user ? $trx->user->name : 'النظام' }}</td>
                            <td>{{ $trx->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $trx->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">لا توجد حركات مالية مسجلة ضمن الفلترة الحالية.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection