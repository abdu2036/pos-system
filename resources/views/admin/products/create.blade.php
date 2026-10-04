@extends('layouts.app')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إضافة منتج جديد</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">الرجوع للمنتجات</a>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-9">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">بيانات المنتج والمخزون</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">اسم المنتج</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">التصنيف</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">اختر التصنيف</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الباركود (Barcode)</label>
                            <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}">
                            @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label required">سعر الشراء</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', 0) }}" required>
                            @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label required">سعر البيع (قطاعي)</label>
                            <input type="number" step="0.01" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price') }}" required>
                            @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">سعر البيع (جملة)</label>
                            <input type="number" step="0.01" name="wholesale_price" class="form-control @error('wholesale_price') is-invalid @enderror" value="{{ old('wholesale_price') }}" placeholder="اختياري">
                            @error('wholesale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label required">الوحدة</label>
                            <select name="unit" class="form-select" required>
                                <option value="قطعة" {{ old('unit') == 'قطعة' ? 'selected' : '' }}>قطعة</option>
                                <option value="صندوق" {{ old('unit') == 'صندوق' ? 'selected' : '' }}>صندوق</option>
                                <option value="طرد" {{ old('unit') == 'طرد' ? 'selected' : '' }}>طرد</option>
                                <option value="كيلو" {{ old('unit') == 'كيلو' ? 'selected' : '' }}>كيلو</option>
                                <option value="لتر" {{ old('unit') == 'لتر' ? 'selected' : '' }}>لتر</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">الكمية المتوفرة</label>
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 0) }}" min="0" required>
                            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">حد التنبيه بنقص المخزون</label>
                            <input type="number" name="alert_quantity" class="form-control @error('alert_quantity') is-invalid @enderror" value="{{ old('alert_quantity', 5) }}" min="1">
                            @error('alert_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">+ حفظ المنتج</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection