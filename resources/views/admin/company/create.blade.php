@extends('layouts.app')

@section('title', 'إضافة بيانات الشركة')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">إضافة بيانات الشركة / المؤسسة الرئيسية</h5>
            <a href="{{ route('company-settings.index') }}" class="btn btn-sm btn-light">إلغاء وعودة</a>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('company-settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <!-- اسم الشركة -->
                    <div class="col-md-6">
                        <label for="name" class="form-label">اسم الشركة / المؤسسة <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- العملة الافتراضية -->
                    <div class="col-md-6">
                        <label for="currency" class="form-label">العملة الافتراضية <span class="text-danger">*</span></label>
                        <input type="text" name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" value="{{ old('currency', 'د.ل') }}" required>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- الهاتف 1 -->
                    <div class="col-md-6">
                        <label for="phone_1" class="form-label">رقم الهاتف الرئيسي <span class="text-danger">*</span></label>
                        <input type="text" name="phone_1" id="phone_1" class="form-control @error('phone_1') is-invalid @enderror" value="{{ old('phone_1') }}" required>
                        @error('phone_1')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- الهاتف 2 -->
                    <div class="col-md-6">
                        <label for="phone_2" class="form-label">رقم هاتف إضافي</label>
                        <input type="text" name="phone_2" id="phone_2" class="form-control @error('phone_2') is-invalid @enderror" value="{{ old('phone_2') }}">
                        @error('phone_2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- البريد الإلكتروني -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- الرقم التجاري / الرخصة -->
                    <div class="col-md-3">
                        <label for="trade_license" class="form-label">رقم السجل التجاري / الرخصة</label>
                        <input type="text" name="trade_license" id="trade_license" class="form-control @error('trade_license') is-invalid @enderror" value="{{ old('trade_license') }}">
                        @error('trade_license')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- الرقم الضريبي -->
                    <div class="col-md-3">
                        <label for="tax_number" class="form-label">الرقم الضريبي</label>
                        <input type="text" name="tax_number" id="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number') }}">
                        @error('tax_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- العنوان -->
                    <div class="col-md-12">
                        <label for="address" class="form-label">عنوان الشركة الرئيسي <span class="text-danger">*</span></label>
                        <textarea name="address" id="address" rows="2" class="form-control @error('address') is-invalid @enderror" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
<div class="mb-3">
    <label class="form-check form-switch">
        <input type="checkbox" name="enable_shifts" value="1" class="form-check-input" {{ old('enable_shifts', $company->enable_shifts ?? false) ? 'checked' : '' }}>
        <span class="form-check-label fw-bold">تفعيل نظام الورديات (Cashier Shifts)</span>
    </label>
    <small class="text-muted">عند التفعيل، سيُطالب الكاشير بفتح وردية وإدخال رصيد البداية قبل بدء المبيعات.</small>
</div>
                    <!-- شعار الشركة (Logo) -->
                    <div class="col-md-12">
                        <label for="logo" class="form-label">شعار الشركة (Logo)</label>
                        <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- تذييل الفاتورة -->
                    <div class="col-md-12">
                        <label for="invoice_footer" class="form-label">تذييل الفاتورة (ملاحظات تظهر أسفل الفواتير)</label>
                        <textarea name="invoice_footer" id="invoice_footer" rows="3" class="form-control @error('invoice_footer') is-invalid @enderror" placeholder="مثال: البضاعة المباعة لا ترد ولا تستبدل بعد 3 أيام">{{ old('invoice_footer') }}</textarea>
                        @error('invoice_footer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success px-4">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection