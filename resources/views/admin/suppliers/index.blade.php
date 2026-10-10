@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة الموردين والديون المستحقة</h2>
            </div>
            <div class="col-auto ms-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-supplier">
                    + إضافة مورد جديد
                </button>
            </div>
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
                        // فحص وجود رصيد أو معاملات مرتبطة
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
                                <span class="badge bg-warning-lt text-warning fs-6">{{ number_format($supplier->balance, 2) }} د.ل (يطلبنا مال)</span>
                            @elseif($supplier->balance < 0)
                                <span class="badge bg-success-lt text-success fs-6">{{ number_format(abs($supplier->balance), 2) }} د.ل (نطالبه بمال)</span>
                            @else
                                <span class="badge bg-secondary-lt fs-6">0.00 د.ل</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap justify-content-center">
                                {{-- زر سداد --}}
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modal-pay-supplier-{{ $supplier->id }}">
                                    سداد دفعة
                                </button>
                                
                                {{-- زر كشف حساب --}}
                                <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-outline-primary">
                                    كشف حساب
                                </a>

                                {{-- زر تعديل --}}
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modal-edit-supplier-{{ $supplier->id }}">
                                    تعديل
                                </button>

                                {{-- زر حذف --}}
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-delete-supplier-{{ $supplier->id }}">
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
                                            <input type="number" step="0.01" name="amount" class="form-control" required>
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
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">اسم الشركة / المصنع</label>
                                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $supplier->company_name) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">رقم الهاتف</label>
                                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">العنوان</label>
                                            <input type="text" name="address" class="form-control" value="{{ old('address', $supplier->address) }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal حذف/منع حذف المورد -->
                    <div class="modal fade" id="modal-delete-supplier-{{ $supplier->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-start">
                                <div class="modal-header">
                                    <h5 class="modal-title">تأكيد حذف المورد</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                @if($cannotDelete)
                                    {{-- إذا كان المورد يملك رصيد أو معاملات مسبقة --}}
                                    <div class="modal-body">
                                        <div class="alert alert-warning mb-0">
                                            <h4 class="alert-title">تعذّر حذف المورد!</h4>
                                            <div class="text-secondary">
                                                لا يمكن حذف المورد <strong class="text-dark">{{ $supplier->name }}</strong> لأنه يحتوي على:
                                                <ul class="mt-2 mb-0">
                                                    @if($hasBalance)
                                                        <li>رصيد مالي قائم ({{ number_format($supplier->balance, 2) }} د.ل).</li>
                                                    @endif
                                                    @if($hasTransactions)
                                                        <li>سجل فواتير شراء أو دفعات سابقة.</li>
                                                    @endif
                                                </ul>
                                                <small class="d-block mt-2 text-danger">حذف المورد سيؤدي إلى تلف القوائم المالية والتقارير المحاسبية للشركة.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                    </div>
                                @else
                                    {{-- إذا كان المورد جديد ولا يحتوي على أي معاملات أو رصيد --}}
                                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-body">
                                            هل أنت متأكد من رغبتك في حذف المورد <strong class="text-danger">{{ $supplier->name }}</strong>؟
                                            <br><small class="text-muted">ملاحظة: هذا الإجراء سيمسح بيانات المورد نهائياً.</small>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
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