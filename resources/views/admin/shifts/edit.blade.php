@extends('layouts.app')

@section('title', 'تعديل بيانات الوردية')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">✏️ تعديل بيانات الوردية #{{ $shift->id }}</h2>
            <p class="text-muted">تعديل المبالغ أو الملاحظات في حال وجود خطأ أثناء عملية الإغلاق أو الفتح.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('shifts.update', $shift->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">مبلغ الافتتاح (د.ل)</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ $shift->opening_balance }}" required>
                </div>

                @if($shift->status == 'closed')
                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ الفعلي عند الإغلاق (د.ل)</label>
                    <input type="number" step="0.01" name="closing_balance" class="form-control" value="{{ $shift->closing_balance }}">
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3">{{ $shift->notes }}</textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('shifts.index') }}" class="btn btn-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection