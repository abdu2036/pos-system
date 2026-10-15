<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
       public static function middleware(): array
    {
        return [
            new Middleware('permission:manage roles', only: ['index']),
            new Middleware('permission:create roles', only: ['create', 'store']),
            new Middleware('permission:edit roles', only: ['edit', 'update']),
            new Middleware('permission:delete roles', only: ['destroy']),
        ];
    }
    // عرض قائمة الأدوار
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all(); // لجلب كافة الصلاحيات المتاحة
        return view('roles.index', compact('roles', 'permissions'));
    }

    // حفظ دور جديد مع صلاحياته
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->back()->with('success', 'تم إنشاء الدور بنجاح.');
    }

    // تحديث دور وصلاحياته
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->back()->with('success', 'تم تحديث الدور والصلاحيات بنجاح.');
    }

    // حذف دور
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // منع حذف دور الـ admin الرئيسي لحماية النظام
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'لا يمكن حذف دور المدير الأساسي (admin).');
        }

        $role->delete();

        return redirect()->back()->with('success', 'تم حذف الدور بنجاح.');
    }
}