@extends('layouts.app')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <form action="{{ route('company-settings.update') }}" method="POST" enctype="multipart/form-data" class="card">
            @csrf
            <div class="card-header">
                <h3 class="card-title">إعدادات بيانات الشركة / المؤسسة</h3>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row row-cards">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label required">اسم الشركة / المؤسسة</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">الرقم التجاري / الرخصة</label>
                            <input type="text" name="trade_license" class="form-control" value="{{ old('trade_license', $company->trade_license) }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">الرقم الضريبي</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $company->tax_number) }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label required">رقم الهاتف الرئيسي</label>
                            <input type="text" name="phone_1" class="form-control" value="{{ old('phone_1', $company->phone_1) }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">رقم هاتف إضافي</label>
                            <input type="text" name="phone_2" class="form-control" value="{{ old('phone_2', $company->phone_2) }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label required">العنوان الرئيسي</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $company->address) }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label required">رمز العملة</label>
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', $company->currency ?? 'د.ل') }}" required>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">الشعار (Logo)</label>
                            <input type="file" name="logo" class="form-control">
                            @if($company->logo)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" height="60">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">نص أسفل الفواتير (تذييل الفاتورة)</label>
                            <textarea name="invoice_footer" class="form-control" rows="3">{{ old('invoice_footer', $company->invoice_footer) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            </div>
        </form>
    </div>
</div>
@endsection