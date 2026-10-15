@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header mb-3">
        <h2 class="page-title fw-bold">🔐 فتح وردية عمل جديدة (Cashier Shift)</h2>
        <div class="text-muted mt-1">يرجى إدخال رصيد عهدة البداية (المبلغ الموجود في الدرج افتراضياً) لبدء المبيعات.</div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <form action="{{ route('shifts.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">رصيد عهدة البداية (المبلغ الموجود في الدرج افتراضياً) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="opening_balance" class="form-control form-control-lg" value="0.00" required>
                            <small class="text-muted">سيتم ربط الوردية بصندوق النقدية الرئيسي تلقائياً في الخلفية.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" placeholder="ملاحظات افتتاح الوردية..."></textarea>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary w-100">فتح الوردية وبدء العمل 🚀</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection