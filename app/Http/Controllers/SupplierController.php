<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
public function index()
{
    $suppliers = Supplier::withCount(['purchaseInvoices', 'transactions'])->get();
    return view('admin.suppliers.index', compact('suppliers'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Supplier::create([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'تم إضافة المورد بنجاح');
    }

    // دفعة مسددة للمورد
    public function addPayment(Request $request, Supplier $supplier)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string'
        ]);

        // تقليل المستحق للمورد
        $supplier->decrement('balance', $request->amount);

        $supplier->transactions()->create([
            'type' => 'payment',
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes ?? 'دفع دفعة للمورد'
        ]);

        return redirect()->back()->with('success', 'تم تسجيل السداد للمورد بنجاح');
    }

public function show($id)
{
    $supplier = Supplier::findOrFail($id);

    // جلب المعاملات الخاصة بالمورد (أو من العلاقة $supplier->transactions)
    $transactions = $supplier->transactions()->latest()->get(); 
    
    // جلب فواتير الشراء المباشرة إن كانت منفصلة
    $purchaseInvoices = $supplier->purchaseInvoices()->latest()->get();

    return view('admin.suppliers.show', compact('supplier', 'transactions', 'purchaseInvoices'));
}
public function edit(Supplier $supplier)
{
    return view('admin.suppliers.edit', compact('supplier'));
}

public function update(Request $request, Supplier $supplier)
    {
        // 1. التحقق من صحة البيانات المدخلة
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:500',
        ]);

        // 2. تحديث بيانات المورد
        $supplier->update($validated);

        // 3. إعادة التوجيه مع رسالة نجاح
        return redirect()->route('suppliers.index')->with('success', 'تم تحديث بيانات المورد بنجاح.');
    }

public function storeAjax(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:50',
        'address' => 'nullable|string|max:255',
    ]);

    $supplier = Supplier::create($validated);

    return response()->json([
        'success' => true,
        'supplier' => $supplier
    ]);
}
public function destroy(Supplier $supplier)
{
    // تحقق أمان إضافي على مستوى الخادم
    if ($supplier->balance != 0 || $supplier->purchaseInvoices()->exists() || $supplier->transactions()->exists()) {
        return redirect()->back()->with('error', 'تعذّر حذف المورد لوجود رصيد أو معاملات مالية مسجلة باسمه.');
    }

    $supplier->delete();

    return redirect()->back()->with('success', 'تم حذف المورد بنجاح.');
}
}
