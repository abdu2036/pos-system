@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title fw-bold">إدارة الزبائن والديون</h2>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                <a href="{{ route('customers.index', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-outline-primary">
                    🖨️ طباعة تقرير الزبائن
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-customer">
                    + إضافة زبون جديد
                </button>
            </div>
        </div>
    </div>

    <!-- كروت الإحصائيات العلوية -->
    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي عدد الزبائن</div>
                    <div class="fw-bold fs-2 text-dark">{{ number_format($totalCustomers ?? 0) }} <small class="fs-6">زبون</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm border-0 shadow-sm border-start border-danger border-3">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي الديون الحالية (المستحقة)</div>
                    <div class="fw-bold fs-2 text-danger">{{ number_format($totalDebts ?? 0, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي الحد الائتماني الممنوح</div>
                    <div class="fw-bold fs-2 text-primary">{{ number_format($totalCreditLimit ?? 0, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- شريط البحث والفلترة -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light">
            <form action="{{ route('customers.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="ابحث باسم الزبون أو رقم الهاتف...">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100">بحث</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">✖</a>
                </div>
            </form>
        </div>
    </div>

    {{-- تنبيهات النجاح والخطأ --}}
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

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>رقم الهاتف</th>
                        <th>الرصيد / الدين الحالي</th>
                        <th>الحد الائتماني</th>
                        <th class="w-1 text-center">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td class="fw-bold">{{ $customer->name }}</td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                        <td>
                            @if($customer->balance > 0)
                                <span class="badge bg-danger-lt text-danger fs-6">{{ number_format($customer->balance, 2) }} د.ل (عليه دين)</span>
                            @elseif($customer->balance < 0)
                                <span class="badge bg-success-lt text-success fs-6">{{ number_format(abs($customer->balance), 2) }} د.ل (له رصيد)</span>
                            @else
                                <span class="badge bg-secondary-lt fs-6">0.00 د.ل</span>
                            @endif
                        </td>
                        <td>{{ number_format($customer->credit_limit, 2) }} د.ل</td>
                        <td>
                            <div class="btn-list flex-nowrap justify-content-center">
                                @if($customer->balance > 0)
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal-pay-{{ $customer->id }}">
                                        تسديد دفعة
                                    </button>
                                @endif
                                
                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary">
                                    كشف حساب
                                </a>

                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $customer->id }}">
                                    تعديل
                                </button>

                                @if(method_exists($customer, 'hasFinancialRecords') && $customer->hasFinancialRecords())
                                    <button class="btn btn-sm btn-outline-danger" disabled title="لا يمكن حذف زبون عليه ديون أو يملك معاملات مالية">
                                        حذف
                                    </button>
                                @else
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت تأكد من رغبتك في حذف هذا الزبون؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            حذف
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Modal تسديد دفعة للزبون -->
                    @if($customer->balance > 0)
                    <div class="modal fade" id="modal-pay-{{ $customer->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
<form action="{{ route('customers.pay', $customer->id) }}" method="POST">                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">قبض دفعة من: {{ $customer->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label required">المبلغ المقبوض (الدين المتبقي: {{ number_format($customer->balance, 2) }} د.ل)</label>
                                            <input type="number" step="0.01" max="{{ $customer->balance }}" name="amount" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">طريقة الدفع</label>
                                            <select name="payment_method" class="form-select">
                                                <option value="cash">نقداً</option>
                                                <option value="card">بطاقة مصرفية</option>
                                                <option value="transfer">تحويل بنكي</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات</label>
                                            <input type="text" name="notes" class="form-control">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">حفظ الدفعة</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Modal تعديل بيانات الزبون -->
                    <div class="modal fade" id="modal-edit-{{ $customer->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">تعديل بيانات: {{ $customer->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label required">اسم الزبون</label>
                                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">رقم الهاتف</label>
                                            <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">العنوان</label>
                                            <input type="text" name="address" class="form-control" value="{{ $customer->address }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">الحد الائتماني (الحد الأقصى للدين)</label>
                                            <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ $customer->credit_limit }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">تحديث البيانات</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">لا توجد بيانات زبائن مطابقة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- روابط التصفح -->
        @if(method_exists($customers, 'links'))
        <div class="card-footer d-flex align-items-center">
            {!! $customers->links() !!}
        </div>
        @endif
    </div>
</div>

<!-- Modal إضافة زبون -->
<div class="modal fade" id="modal-add-customer" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إضافة زبون جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">اسم الزبون</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الحد الائتماني (الحد الأقصى للدين)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection