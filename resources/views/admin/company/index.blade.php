@extends('layouts.app')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>{{ session('success') }}</div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">بيانات الشركة / المؤسسة الرئيسية</h3>
                <a href="{{ route('company-settings.edit') }}" class="btn btn-primary">
                    تعديل البيانات
                </a>
            </div>

            <div class="card-body">
                @if($company)
                    <div class="row g-4">
                        <div class="col-md-3 text-center border-end">
                            <div class="mb-3">
                                @if($company->logo)
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="img-fluid rounded" style="max-height: 140px;">
                                @else
                                    <div class="avatar avatar-xl bg-secondary-lt text-uppercase font-weight-bold">
                                        {{ mb_substr($company->name, 0, 2) }}
                                    </div>
                                @endif
                            </div>
                            <h3 class="m-0 font-weight-bold">{{ $company->name }}</h3>
                            <div class="mt-2 d-flex flex-column gap-1 align-items-center">
                                <span class="badge bg-green-lt">العملة: {{ $company->currency }}</span>
                                
                                <!-- حالة نظام الورديات -->
                                @if(isset($company->enable_shifts) && $company->enable_shifts)
                                    <span class="badge bg-success text-white">نظام الورديات: مفعل</span>
                                @else
                                    <span class="badge bg-secondary text-white">نظام الورديات: معطل</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="row row-cards">
                                <div class="col-sm-6 col-md-4">
                                    <div class="text-muted small">رقم الهاتف الرئيسي</div>
                                    <div class="font-weight-bold fs-3">{{ $company->phone_1 }}</div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="text-muted small">رقم الهاتف الإضافي</div>
                                    <div class="font-weight-bold fs-3">{{ $company->phone_2 ?? '-' }}</div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="text-muted small">البريد الإلكتروني</div>
                                    <div class="font-weight-bold fs-3">{{ $company->email ?? '-' }}</div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="text-muted small">السجل التجاري / الرخصة</div>
                                    <div class="font-weight-bold fs-3">{{ $company->trade_license ?? '-' }}</div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="text-muted small">الرقم الضريبي</div>
                                    <div class="font-weight-bold fs-3">{{ $company->tax_number ?? '-' }}</div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="text-muted small">العنوان الرئيسي</div>
                                    <div class="font-weight-bold fs-3">{{ $company->address }}</div>
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="text-muted small">تذييل الفواتير (Invoice Footer)</div>
                                    <div class="p-2 bg-light rounded text-dark mt-1">
                                        {{ $company->invoice_footer ?? 'لا يوجد تذييل محدد' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-3">لم يتم إدخال بيانات الشركة بعد.</p>
                        <a href="{{ route('company-settings.edit') }}" class="btn btn-primary">إضافة البيانات الآن</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection