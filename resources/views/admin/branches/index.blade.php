@extends('layouts.app')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <div>{{ session('success') }}</div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <div>{{ session('error') }}</div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">إدارة الفروع</h3>
                <a href="{{ route('branches.create') }}" class="btn btn-primary">
                    إضافة فرع جديد
                </a>
            </div>

            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap datatable">
                    <thead>
                        <tr>
                            <th>كود الفرع</th>
                            <th>اسم الفرع</th>
                            <th>الهاتف</th>
                            <th>العنوان</th>
                            <th>عدد الموظفين</th>
                            <th>النوع / الحالة</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr>
                                <td><span class="badge bg-blue-lt">{{ $branch->code }}</span></td>
                                <td class="font-weight-bold">{{ $branch->name }}</td>
                                <td>{{ $branch->phone ?? '-' }}</td>
                                <td>{{ $branch->address ?? '-' }}</td>
                                <td>{{ $branch->users_count }} موظف</td>
                                <td>
                                    @if($branch->is_main)
                                        <span class="badge bg-purple text-purple-fg">فرع رئيسي</span>
                                    @endif

                                    @if($branch->is_active)
                                        <span class="badge bg-success text-success-fg">نشط</span>
                                    @else
                                        <span class="badge bg-secondary text-secondary-fg">متوقف</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary ms-1">تعديل</a>
                                    
                                    @if(!$branch->is_main)
                                        <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت تأكد من حذف هذا الفرع؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">لا يوجد فروع مسجلة حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($branches->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $branches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection