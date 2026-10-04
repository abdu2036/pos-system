<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\CompanySetting;

class BranchController extends Controller
{
    // عرض قائمة الفروع
    public function index()
    {
        $branches = Branch::withCount('users')->latest()->paginate(10);
        return view('admin.branches.index', compact('branches'));
    }

    // صفحة إضافة فرع جديد
    public function create()
    {
        return view('admin.branches.create');
    }

    // حفظ الفرع الجديد



public function store(Request $request)
{
    $company = CompanySetting::first();

    // التأكد من وجود شركة معرّفة أولاً
    if (!$company) {
        return redirect()->route('company-settings.create')
            ->with('error', 'يرجى إدخال بيانات الشركة أولاً قبل إضافة الفروع.');
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'code' => 'required|string|unique:branches,code',
        'phone' => 'nullable|string|max:50',
        'address' => 'nullable|string',
    ]);

    Branch::create([
        'company_id' => $company->id, // ربط تلقائي بـ ID الشركة
        'name'       => $request->name,
        'code'       => $request->code,
        'phone'      => $request->phone,
        'address'    => $request->address,
        'is_main'    => $request->has('is_main'),
        'is_active'  => $request->has('is_active'),
    ]);

    return redirect()->route('branches.index')->with('success', 'تم إضافة الفرع بنجاح');
}

    // صفحة تعديل بيانات فرع
    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    // تحديث بيانات الفرع
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        if ($request->has('is_main') && !$branch->is_main) {
            Branch::query()->update(['is_main' => false]);
        }

        $branch->update([
            'name'      => $request->name,
            'code'      => strtoupper($request->code),
            'phone'     => $request->phone,
            'address'   => $request->address,
            'is_main'   => $request->has('is_main') ? true : $branch->is_main,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('branches.index')->with('success', 'تم تحديث بيانات الفرع بنجاح');
    }

    // حذف الفرع
    public function destroy(Branch $branch)
    {
        if ($branch->is_main) {
            return redirect()->back()->with('error', 'لا يمكن حذف الفرع الرئيسي للمؤسسة');
        }

        if ($branch->users()->count() > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف الفرع لأنه مرتبطي بموظفين/مستخدمين');
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'تم حذف الفرع بنجاح');
    }
    
}
