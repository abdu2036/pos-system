<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    // عرض تفاصيل الشركة
    public function index()
    {
        $company = CompanySetting::first();
        return view('admin.company.index', compact('company'));
    }

    // صفحة إنشاء بيانات الشركة لأول مرة
 public function create()
{
    $company = CompanySetting::first();
    if ($company) {
        return redirect()->route('company-settings.edit');
    }

    // التأكد من وجود الواجهة
    if (!view()->exists('admin.company.create')) {
        return "الملف resources/views/admin/company/create.blade.php غير موجود!";
    }

    return view('admin.company.create');
}

    // حفظ البيانات الجديدة لأول مرة
    public function store(Request $request)
    {
        if (CompanySetting::exists()) {
            return redirect()->route('company-settings.edit');
        }

        $request->validate([
            'name'           => 'required|string|max:255',
            'phone_1'        => 'required|string|max:50',
            'phone_2'        => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'required|string',
            'currency'       => 'required|string|max:20',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'trade_license'  => 'nullable|string|max:100',
            'tax_number'     => 'nullable|string|max:100',
            'invoice_footer' => 'nullable|string',
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('company', 'public');
        }

        CompanySetting::create($data);

        return redirect()->route('company-settings.index')->with('success', 'تم تسجيل بيانات الشركة بنجاح');
    }

    // صفحة تعديل بيانات الشركة
    public function edit()
    {
        // استخدام firstOrCreate لتجنب التحويل الدائري ولضمان الحصول على كائن دائماً
        $company = CompanySetting::first();

        if (!$company) {
            return redirect()->route('company-settings.create');
        }

        return view('admin.company.edit', compact('company'));
    }

    // تحديث البيانات
    public function update(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'phone_1'        => 'required|string|max:50',
            'phone_2'        => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'required|string',
            'currency'       => 'required|string|max:20',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'trade_license'  => 'nullable|string|max:100',
            'tax_number'     => 'nullable|string|max:100',
            'invoice_footer' => 'nullable|string',
        ]);

        $company = CompanySetting::first();

        // في حال عدم وجود سجل سابق، يتم إنشاؤه فوراً مع التحديث
        if (!$company) {
            $company = new CompanySetting();
        }

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('company', 'public');
        }

        $company->fill($data)->save();

        return redirect()->route('company-settings.index')->with('success', 'تم تحديث بيانات الشركة بنجاح');
    }
}