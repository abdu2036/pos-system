@extends('layouts.app')

@section('title', 'إدارة الخزائن والحسابات')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">💰 إدارة الخزائن والصناديق المالية</h2>
                <p class="text-muted">إدارة الخزائن والحسابات البنكية وتحديد رصيد أول المدة.</p>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
    <a href="{{ route('treasuries.report') }}" class="btn btn-outline-primary">
        📊 تقرير الحركة المالية
    </a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTreasuryModal">
        + إضافة خزينة / حساب جديد
    </button>
</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- جدول الخزائن والحسابات -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 25%;">اسم الخزينة / الحساب</th>
                            <th style="width: 15%;">نوع الحساب</th>
                            <th style="width: 20%;">رصيد أول المدة</th>
                            <th style="width: 20%;">الرصيد الحالي</th>
                            <th class="text-center" style="width: 20%;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($treasuries as $treasury)
                        <tr>
                            <td class="fw-bold">
                                <i class="fa {{ $treasury->type == 'cash' ? 'fa-cash-register text-success' : 'fa-university text-primary' }} me-2"></i>
                                {{ $treasury->name }}
                            </td>
                            <td>
                                @if($treasury->type == 'cash')
                                    <span class="badge bg-success">صندوق نقدي (Cash)</span>
                                @else
                                    <span class="badge bg-primary">حساب بنكي (Bank)</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-secondary">
                                {{ number_format($treasury->opening_balance, 2) }} د.ل
                            </td>
                            <td class="fw-bold text-success fs-6">
                                {{ number_format($treasury->current_balance, 2) }} د.ل
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editTreasuryModal{{ $treasury->id }}">
                                    <i class="fa fa-edit"></i> تعديل
                                </button>
                                <form action="{{ route('treasuries.destroy', $treasury->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الخزينة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-trash"></i> حذف
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal تعديل خزينة -->
                        <div class="modal fade" id="editTreasuryModal{{ $treasury->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('treasuries.update', $treasury->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">تعديل الخزينة: {{ $treasury->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label">اسم الخزينة / الحساب</label>
                                                <input type="text" name="name" value="{{ $treasury->name }}" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">نوع الحساب</label>
                                                <select name="type" class="form-select" required>
                                                    <option value="cash" {{ $treasury->type == 'cash' ? 'selected' : '' }}>صندوق نقدي (Cash)</option>
                                                    <option value="bank" {{ $treasury->type == 'bank' ? 'selected' : '' }}>حساب بنكي (Bank)</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">رصيد أول المدة</label>
                                                <input type="number" step="0.01" name="opening_balance" value="{{ $treasury->opening_balance }}" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">ملاحظات</label>
                                                <textarea name="notes" class="form-control" rows="2">{{ $treasury->notes }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">لا توجد خزائن أو حسابات مضافة حالياً.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal إضافة خزينة جديدة -->
<div class="modal fade" id="addTreasuryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('treasuries.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة خزينة أو صندوق جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label">اسم الخزينة / الحساب</label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: الخزينة الرئيسية، صندوق كاشير 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع الحساب</label>
                        <select name="type" class="form-select" required>
                            <option value="cash">صندوق نقدي (Cash)</option>
                            <option value="bank">حساب بنكي (Bank)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رصيد أول المدة</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" placeholder="0.00" value="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات توضيحية..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ الخزينة</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection