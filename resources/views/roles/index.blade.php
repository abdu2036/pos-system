@extends('layouts.app')

@section('title', 'إدارة الأدوار والصلاحيات')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">🔐 إدارة الأدوار والصلاحيات</h2>
                <p class="text-muted">تحديد الأدوار الوظيفية وصلاحياتها المرتبطة داخل المنظومة.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="fa fa-plus"></i> إضافة دور جديد
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- جدول الأدوار -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;">اسم الدور</th>
                            <th style="width: 60%;">الصلاحيات المرتبطة</th>
                            <th class="text-center" style="width: 20%;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td class="fw-bold">
<span class="badge  fs-10">
    {{ Lang::has('permissions.' . $role->name) ? __('permissions.' . $role->name) : $role->name }}
</span>                            </td>
                            <td>
                                <div style="max-height: 100px; overflow-y: auto;">
                                    @foreach($role->permissions as $permission)
                                        <span class="badge bg-light text-dark border m-1">{{ __('permissions.' . $permission->name) }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                    <i class="fa fa-edit"></i> تعديل
                                </button>
                                @if($role->name !== 'admin' && $role->name !== 'super-admin')
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الدور؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-trash"></i> حذف
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal تعديل دور -->
                        <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1">
                            <div class="modal-dialog modal-xl">
                                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">تعديل الدور: {{ __('permissions.' . $role->name) }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">اسم الدور (النظامي بالإنجليزية)</label>
                                                <input type="text" name="name" value="{{ $role->name }}" class="form-control" required>
                                            </div>

                                            <hr>
                                            <h5 class="text-primary mb-3">🛠️ صلاحيات المنظومة مقسمة حسب الأقسام:</h5>

                                            @php
                                                $groups = [
                                                    'إدارة الأدوار' => fn($p) => str_contains($p->name, 'role'),
                                                    'إدارة المستخدمين' => fn($p) => str_contains($p->name, 'user'),
                                                    'إدارة الشركة' => fn($p) => str_contains($p->name, 'company'),
                                                    'إدارة الفروع' => fn($p) => str_contains($p->name, 'branch'),
                                                    'إدارة التصنيفات' => fn($p) => str_contains($p->name, 'categor'),
                                                    'إدارة المنتجات' => fn($p) => str_contains($p->name, 'product'),
                                                    'فواتير الشراء والموردين' => fn($p) => str_contains($p->name, 'purchase') || (str_contains($p->name, 'supplier') && !str_contains($p->name, 'customers')),
                                                    'الزبائن والديون' => fn($p) => str_contains($p->name, 'customer'),
                                                    'إدارة المبيعات والفواتير' => fn($p) => str_contains($p->name, 'invoice') && !str_contains($p->name, 'purchase'),
                                                    'الخزينة والتقارير' => fn($p) => str_contains($p->name, 'treasur') || str_contains($p->name, 'report'),
                                                    'الورديات' => fn($p) => str_contains($p->name, 'shift'),
                                                ];
                                            @endphp

                                            <div class="row">
                                                @foreach($groups as $groupName => $filter)
                                                    @php $groupPerms = $permissions->filter($filter); @endphp
                                                    @if($groupPerms->count() > 0)
                                                        <div class="col-md-6 mb-3">
                                                            <div class="card border shadow-sm h-100 permission-group-card">
                                                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                                    <span class="fw-bold text-secondary">{{ $groupName }}</span>
                                                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 toggle-group-btn" style="font-size: 12px;">تحديد الكل</button>
                                                                </div>
                                                                <div class="card-body row">
                                                                    @foreach($groupPerms as $permission)
                                                                    <div class="col-12 mb-2">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input group-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="edit_perm_{{ $role->id }}_{{ $permission->id }}"
                                                                                {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="edit_perm_{{ $role->id }}_{{ $permission->id }}">
                                                                                {{ __('permissions.' . $permission->name) }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">لا توجد أدوار مسجلة حالياً.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal إضافة دور جديد -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">إضافة دور وظيفي جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الدور (باللغة الإنجليزية مثل: accountant, cashier)</label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: cashier, supervisor..." required>
                    </div>

                    <hr>
                    <h5 class="text-primary mb-3">🛠️ حدد صلاحيات الدور مقسمة حسب أقسام المنظومة:</h5>

                    <div class="row">
                        @foreach($groups as $groupName => $filter)
                            @php $groupPerms = $permissions->filter($filter); @endphp
                            @if($groupPerms->count() > 0)
                                <div class="col-md-6 mb-3">
                                    <div class="card border shadow-sm h-100 permission-group-card">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-secondary">{{ $groupName }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 toggle-group-btn" style="font-size: 12px;">تحديد الكل</button>
                                        </div>
                                        <div class="card-body row">
                                            @foreach($groupPerms as $permission)
                                            <div class="col-12 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input group-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="add_perm_{$permission->id}}">
                                                    <label class="form-check-label" for="add_perm_{{$permission->id}}">
                                                        {{ __('permissions.' . $permission->name) }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ الدور</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript لتفعيل زر تحديد الكل لكل صندوق على حدة -->
<script>
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('toggle-group-btn')) {
            const card = e.target.closest('.permission-group-card');
            const checkboxes = card.querySelectorAll('.group-checkbox');
            
            // تحقق ما إذا كانت كلها محددة أم لا، لتبديل الحالة (تحديد الكل / إلغاء الكل)
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            // تغيير نص الزر بناءً على الحالة الجديدة
            e.target.textContent = allChecked ? 'تحديد الكل' : 'إلغاء الكل';
        }
    });
</script>
@endsection