@extends('layouts.app')

@section('title', 'لوحة التحكم الرئيسية')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    لوحة التحكم الرئيسية
                </h2>
                <div class="text-secondary mt-1">
                    أهلاً بك في منظومة المبيعات وإدارة الفروع — {{ $company->name ?? 'المؤسسة الرئيسية' }}
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('branches.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        إضافة فرع جديد
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- كروت الإحصائيات -->
        <div class="row row-deck row-cards mb-4">
            <!-- إجمالي الفروع -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 7l18 0" /><path d="M3 7l1 14l16 0l1 -14" /><path d="M9 7v-4a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">إجمالي الفروع</div>
                                <div class="text-secondary">{{ $totalBranches }} فرع</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الفروع النشطة -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-success text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">الفروع النشطة</div>
                                <div class="text-secondary">{{ $activeBranches }} فرع</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- إجمالي المستخدمين -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-warning text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">المستخدمين المربوطين</div>
                                <div class="text-secondary">{{ $totalUsers }} مستخدم</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- حالة بيانات الشركة -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-info text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">بيانات الشركة</div>
                                <div class="text-secondary">
                                    @if($company)
                                        <span class="badge bg-success-lt">مكتملة</span>
                                    @else
                                        <span class="badge bg-danger-lt">غير مدخلة</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول أحدث الفروع والوصول السريع -->
        <div class="row row-cards">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">أحدث الفروع المضافة</h3>
                        <a href="{{ route('branches.index') }}" class="btn btn-link btn-sm">عرض كل الفروع</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap datatable">
                            <thead>
                                <tr>
                                    <th>الكود</th>
                                    <th>اسم الفرع</th>
                                    <th>المستخدمين</th>
                                    <th>الحالة</th>
                                    <th>النوع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBranches as $branch)
                                    <tr>
                                        <td><span class="text-secondary">{{ $branch->code }}</span></td>
                                        <td><strong>{{ $branch->name }}</strong></td>
                                        <td>{{ $branch->users_count }} مستخدم</td>
                                        <td>
                                            @if($branch->is_active)
                                                <span class="badge bg-success-lt">نشط</span>
                                            @else
                                                <span class="badge bg-danger-lt">متوقف</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($branch->is_main)
                                                <span class="badge bg-primary-lt">الفرع الرئيسي</span>
                                            @else
                                                <span class="badge bg-secondary-lt">فرعي</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-secondary">
                                            لا توجد فروع مضافة حتى الآن.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- الاختصارات والوصول السريع -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">روابط سريعة</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('company-settings.index') }}" class="btn btn-outline-primary w-100 justify-content-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                إعدادات الشركة
                            </a>
                            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary w-100 justify-content-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 7l18 0" /><path d="M3 7l1 14l16 0l1 -14" /><path d="M9 7v-4a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4" /></svg>
                                إدارة الفروع
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection