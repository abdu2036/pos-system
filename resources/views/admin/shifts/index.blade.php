@extends('layouts.app')

@section('title', 'إدارة الورديات')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">🕒 إدارة الورديات</h2>
                <p class="text-muted">متابعة ورديات الموظفين، فتح وإغلاق الصناديق وجرد النقدية ومتابعة البطاقات والديون.</p>
            </div>
            <div>
                @php
                    $userActiveShift = \App\Models\Shift::where('user_id', Auth::id())->where('status', 'open')->first();
                @endphp
                @can('open shifts')
                    
                
                @if(!$userActiveShift)
                    <a href="{{ route('shifts.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> فتح وردية جديدة
                    </a>
                @else
                    <a href="{{ route('shifts.close.form', $userActiveShift->id) }}" class="btn btn-danger">
                        🔒 إغلاق ورديتي الحالية (جرد الدرج)
                    </a>
                @endif
                @endcan
            </div>
            
        </div>
    </div>
    <!-- نموذج البحث والفرز -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('shifts.index') }}" method="GET">
            <div class="row g-3">
                <!-- البحث بحالة الوردية -->
                <div class="col-md-3">
                    <label class="form-label text-muted">حالة الوردية</label>
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>مفتوحة</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                    </select>
                </div>

                <!-- البحث بالكاشير -->
                <div class="col-md-3">
                    <label class="form-label text-muted">الكاشير / الموظف</label>
                    <select name="user_id" class="form-select">
                        <option value="">جميع الموظفين</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- من تاريخ -->
                <div class="col-md-2">
                    <label class="form-label text-muted">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>

                <!-- إلى تاريخ -->
                <div class="col-md-2">
                    <label class="form-label text-muted">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>

                <!-- أزرار البحث وإعادة التعيين -->
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-search"></i> بحث
                    </button>
                    <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary" title="إعادة تعيين">
                        <i class="fa fa-times text-red">X</i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- جدول الورديات -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الفرع</th>
                            <th>الموظف / الكاشير</th>
                            <th>حالة الوردية</th>
                            <th>مبلغ الافتتاح</th>
                            <th>إجمالي المبيعات</th>
                            <th>💳 البطاقة / الصك</th>
                            <th>المبلغ المتوقع</th>
                            <th>المبلغ الفعلي</th>
                            <th>الفرق</th>
                            <th>ملاحظات الدين / الزبائن</th>
                            <th>وقت الفتح / الإغلاق</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                        <tr>
                            <td class="fw-bold">{{ $shift->id }}</td>
                            <td>
                                <span class="badge bg-blue-lt">
                                    {{ $shift->branch->name ?? ($shift->user->branch->name ?? 'الفرع الرئيسي') }}
                                </span>
                            </td>
                            <td class="fw-semibold text-primary">
                                {{ $shift->user->name ?? 'غير متوفر' }}
                            </td>
                            <td>
                                @if($shift->status == 'open')
                                    <span class="badge bg-success text-white">🟢 مفتوحة</span>
                                @else
                                    <span class="badge bg-red text-white">🔴 مغلقة</span>
                                @endif
                            </td>
                            <td>{{ number_format($shift->opening_balance, 2) }} د.ل</td>
                            <td class="fw-bold text-success">{{ number_format($shift->total_sales ?? 0, 2) }} د.ل</td>
                            <td>
                                @php
                                    // حساب مبيعات البطاقة أو الصك المرتبطة بهذه الوردية
                                    $cardSales = $shift->invoices ? $shift->invoices->whereIn('payment_method', ['card', 'check'])->sum('total_amount') : 0;
                                @endphp
                                <span class="text-info fw-bold">{{ number_format($cardSales, 2) }} د.ل</span>
                            </td>
                            <td>{{ number_format($shift->expected_balance ?? ($shift->opening_balance + ($shift->total_sales ?? 0)), 2) }} د.ل</td>
                            <td>{{ $shift->closed_at ? number_format($shift->closing_balance, 2) . ' د.ل' : '-' }}</td>
                            <td>
                                @if($shift->closed_at)
                                    @php $diff = $shift->difference ?? ($shift->closing_balance - $shift->expected_balance); @endphp
                                    @if($diff == 0) <span class="badge bg-success text-white">متطابق</span>
                                    @elseif($diff > 0) <span class="badge bg-info text-white">+{{ number_format($diff, 2) }}</span>
                                    @else <span class="badge bg-danger text-white">{{ number_format($diff, 2) }}</span>
                                    @endif
                                @else -
                                @endif
                            </td>
                            <td>
                                @php
                                    // فواتير الدين المرتبطة بالوردية وأسماء الزبائن
                                    $creditInvoices = $shift->invoices ? $shift->invoices->where('payment_method', 'credit') : collect();
                                @endphp
                                @if($creditInvoices->count() > 0)
                                    <div style="max-height: 70px; overflow-y: auto;">
                                        @foreach($creditInvoices as $inv)
                                            <span class="badge bg-warning text-dark m-1" style="font-size: 11px;">
                                                دين: {{ $inv->customer->name ?? 'زبون عام' }} ({{ number_format($inv->total_amount, 2) }})
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="font-size: 11px;">
                                فتح: {{ $shift->opened_at ?? $shift->created_at }}<br>
                                إغلاق: {{ $shift->closed_at ?? 'قيد العمل' }}
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('show shifts')
                                        <a href="{{ route('shifts.show', $shift->id) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                            <i class="fa fa-eye"></i> تفاصيل
                                        </a>
                                    @endcan
                                    @can('edit shifts')
                                        <a href="{{ route('shifts.edit', $shift->id) }}" class="btn btn-sm btn-outline-warning" title="تعديل الوردية">
                                            <i class="fa fa-edit"></i> تعديل
                                        </a>
                                    @endcan
                                    @can('close shifts')
                                    @if($shift->status == 'open' && (Auth::id() == $shift->user_id || Auth::user()->hasRole('admin|super-admin')))
                                        <a href="{{ route('shifts.close.form', $shift->id) }}" class="btn btn-sm btn-outline-danger" title="إغلاق الوردية">
                                            🔒 إغلاق
                                        </a>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">لا توجد ورديات مسجلة حالياً.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- الترقيم (Pagination) -->
            @if(method_exists($shifts, 'links'))
                <div class="mt-3 d-flex justify-content-center">
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection