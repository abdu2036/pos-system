@extends('layouts.app')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <form action="{{ route('branches.update', $branch) }}" method="POST" class="card">
            @csrf
            @method('PUT')
            <div class="card-header">
                <h3 class="card-title">تعديل بيانات الفرع: {{ $branch->name }}</h3>
            </div>
            
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row row-cards">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label required">اسم الفرع</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $branch->name) }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label required">كود الفرع</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', $branch->code) }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">رقم هاتف الفرع</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">عنوان الفرع</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $branch->address) }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="is_main" value="1" {{ old('is_main', $branch->is_main) ? 'checked' : '' }}>
                                <span class="form-check-label">تعيين كفرع رئيسي للمؤسسة</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
                                <span class="form-check-label">حالة الفرع (نشط ومتاح للعمليات)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('branches.index') }}" class="btn btn-link link-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary">تحديث الفرع</button>
            </div>
        </form>
    </div>
</div>
@endsection