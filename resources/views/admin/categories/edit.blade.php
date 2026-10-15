@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-4">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">تعديل التصنيف: {{ $category->name }}</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    ← الرجوع للقائمة
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label required">اسم التصنيف</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required />
                </div>

                <div class="mb-3">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="{{ route('categories.index') }}" class="btn btn-link link-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection