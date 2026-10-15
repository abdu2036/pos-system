@extends('layouts.app')

@section('content')
    <div class="container-xl">
        <div class="page-header d-print-none mb-3">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title fw-bold">إدارة الموردين والديون المستحقة</h2>
                </div>
                <div class="col-auto ms-auto d-flex gap-2">
                    <a href="{{ route('suppliers.index', array_merge(request()->all(), ['print' => 1])) }}" target="_blank"
                        class="btn btn-outline-primary">
                        🖨️ طباعة تقرير الموردين
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-supplier">
                        + إضافة مورد جديد
                    </button>
                </div>
            </div>
        </div>

        <!-- كروت الإحصائيات العلوية -->
        <div class="row row-cards mb-4">
            <div class="col-sm-6 col-lg-6">
                <div class="card card-sm border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted fs-6">إجمالي عدد الموردين</div>
                        <div class="fw-bold fs-2 text-dark">{{ number_format($totalSuppliers ?? 0) }} <small
                                class="fs-6">مورد</small></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-6">
                <div class="card card-sm border-0 shadow-sm border-start border-warning border-3">
                    <div class="card-body">
                        <div class="text-muted fs-6">إجمالي المستحق للموردين (يطلبوننا مالاً)</div>
                        <div class="fw-bold fs-2 text-warning">{{ number_format($totalSuppliersBalance ?? 0, 2) }} <small
                                class="fs-6">د.ل</small></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- شريط البحث والفلترة -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body bg-light">
                <form action="{{ route('suppliers.index') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="ابحث باسم المورد، اسم الشركة، أو رقم الهاتف...">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100">بحث</button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">✖</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- تنبيهات النظام --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>المورد / الشركة</th>
                            <th>رقم الهاتف</th>
                            <th>الرصيد / المستحق للمورد</th>
                            <th class="w-1 text-center">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            @php
                                $hasBalance = ($supplier->balance != 0);
                                $hasTransactions = (($supplier->purchase_invoices_count ?? 0) > 0 || ($supplier->transactions_count ?? 0) > 0);
                                $cannotDelete = $hasBalance || $hasTransactions;
                            @endphp
                            <tr>
                                <td class="fw-bold">
                                    {{ $supplier->name }}
                                    @if($supplier->company_name)
                                        <small class="text-muted d-block">{{ $supplier->company_name }}</small>
                                    @endif
                                </td>
                                <td>{{ $supplier->phone ?? '-' }}</td>
                                <td>
                                    @if($supplier->balance > 0)
                                        <span
                                            class="badge bg-warning-lt text-warning fs-6">{{ number_format($supplier->balance, 2) }}
                                            د.ل (يطلبنا مال)</span>
                                    @elseif($supplier->balance < 0)
                                        <span
                                            class="badge bg-success-lt text-success fs-6">{{ number_format(abs($supplier->balance), 2) }}
                                            د.ل (نطالبه بمال)</span>
                                    @else
                                        <span class="badge bg-secondary-lt fs-6">0.00 د.ل</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap justify-content-center">
                                        {{-- زر سداد (يظهر فقط إذا كان للمورد رصيد مطلوب / يطلبنا مال) --}}
                                        @if($supplier->balance > 0)
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#modal-pay-supplier-{{ $supplier->id }}">
                                                سداد دفعة
                                            </button>
                                        @endif
                                        <a href="{{ route('suppliers.show', $supplier->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            كشف حساب
                                        </a>

                                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                            data-bs-target="#modal-edit-supplier-{{ $supplier->id }}">
                                            تعديل
                                        </button>

                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#modal-delete-supplier-{{ $supplier->id }}">
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal سداد للمورد -->
                            <div class="modal fade" id="modal-pay-supplier-{{ $supplier->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('suppliers.pay', $supplier->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">دفع مبلغ للمورد: {{ $supplier->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label">المبلغ المدفوع</label>
                                                    <input type="number" step="0.01" name="amount" class="form-control"
                                                        required>
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
                                                <button type="submit" class="btn btn-warning">حفظ السداد</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal تعديل بيانات المورد -->
                            <div class="modal fade" id="modal-edit-supplier-{{ $supplier->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content text-start">
                                        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">تعديل بيانات المورد: {{ $supplier->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label required">اسم المورد</label>
                                                    <input type="text" name="name" class="form-control"
                                                        value="{{ old('name', $supplier->name) }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">اسم الشركة / المصنع</label>
                                                    <input type="text" name="company_name" class="form-control"
                                                        value="{{ old('company_name', $supplier->company_name) }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">رقم الهاتف</label>
                                                    <input type="text" name="phone" class="form-control"
                                                        value="{{ old('phone', $supplier->phone) }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">العنوان</label>
                                                    <input type="text" name="address" class="form-control"
                                                        value="{{ old('address', $supplier->address) }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">إلغاء</button>
                                                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal حذف المورد -->
                            <div class="modal fade" id="modal-delete-supplier-{{ $supplier->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content text-start">
                                        <div class="modal-header">
                                            <h5 class="modal-title">تأكيد حذف المورد</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        @if($cannotDelete)
                                            <div class="modal-body">
                                                <div class="alert alert-warning mb-0">
                                                    <h4 class="alert-title">تعذّر حذف المورد!</h4>
                                                    <div class="text-secondary">
                                                        لا يمكن حذف المورد <strong class="text-dark">{{ $supplier->name }}</strong>
                                                        لأنه يحتوي على رصيد أو معاملات سابقة.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">إغلاق</button>
                                            </div>
                                        @else
                                            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    هل أنت متأكد من رغبتك في حذف المورد <strong
                                                        class="text-danger">{{ $supplier->name }}</strong>؟
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">إلغاء</button>
                                                    <button type="submit" class="btn btn-danger">تأكيد الحذف</button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">لا يوجد موردون مسجلون حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal إضافة مورد جديد -->
    <div class="modal fade" id="modal-add-supplier" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة مورد جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">اسم المورد</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">اسم الشركة / المصنع</label>
                            <input type="text" name="company_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">العنوان</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">حفظ المورد</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection