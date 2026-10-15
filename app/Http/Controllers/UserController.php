<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Branch; // تأكد من استدعاء موديل الفرع
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage users', only: ['index']),
            new Middleware('permission:create users', only: ['create', 'store']),
            new Middleware('permission:edit users', only: ['edit', 'update']),
            new Middleware('permission:delete users', only: ['destroy']),
        ];
    }

    // عرض قائمة المستخدمين
public function index()
{
    // استبدل User::all() بالـ paginate
    $users = User::paginate(10); // أو العدد الذي تفضله في كل صفحة
    $roles = Role::all();
    $branches = Branch::all();

    return view('users.index', compact('users', 'roles', 'branches'));
}

    // تخزين مستخدم جديد
public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'branch_id' => 'required|exists:branches,id', // التحقق من وجود الفرع
            'role'      => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'branch_id' => $request->branch_id, // حفظ الفرع
        
        ]);

        // تعيين الدور للمستخدم باستخدام Spatie
        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    // تعديل مستخدم
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'branch_id' => 'required|exists:branches,id', // التحقق من وجود الفرع عند التعديل
            'role'      => 'required|exists:roles,name',
        ]);

        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'branch_id' => $request->branch_id, // تحديث الفرع
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // مزامنة الأدوار (إزالة الأدوار القديمة ووضع الدور الجديد)
        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
    }
    // حذف مستخدم
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // منع الحذف لو كان المستخدم الحالي أو المدير الرئيسي لمنع إغلاق المنظومة على نفسك
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'لا يمكنك حذف حسابك الحالي!');
        }

        $user->delete();

        return redirect()->back()->with('success', 'تم حذف المستخدم بنجاح.');
    }
}