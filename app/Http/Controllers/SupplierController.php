<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{   
public function index(Request $request)
{
    $query = Supplier::withCount(['purchaseInvoices', 'transactions']);

    // البحث باسم المورد أو الشركة أو الهاتف
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // حساب الإجماليات والكروت
    $statsQuery = clone $query;
    $totalSuppliers = $statsQuery->count();
    $totalSuppliersBalance = $statsQuery->sum('balance'); // إجمالي المستحقة للموردين

    $suppliers = $query->latest()->get();

    // فحص طلب الطباعة
    if ($request->has('print')) {
        $company = \App\Models\CompanySetting::first();
        return view('admin.suppliers.print_report', compact('suppliers', 'totalSuppliers', 'totalSuppliersBalance', 'company'));
    }

    return view('admin.suppliers.index', compact('suppliers', 'totalSuppliers', 'totalSuppliersBalance'));
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
// دفعة مسددة للمورد مع خصمها من الخزينة أو البنك وتحديث الرصيد
    public function addPayment(Request $request, Supplier $supplier)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'treasury_id' => 'nullable|exists:treasuries,id', // اختيار الخزينة أو البنك للدفع منه
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($supplier, $request) {
            // 1. تقليل المستحق للمورد
            $supplier->decrement('balance', $request->amount);

            // 2. تسجيل حركة السداد في معاملات المورد
            $supplier->transactions()->create([
                'type' => 'payment',
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes ?? 'دفع دفعة للمورد'
            ]);

            // 3. التوجيه الذكي وتخفيض رصيد الخزينة أو البنك
            $treasuryId = $request->treasury_id;
            
            if (!$treasuryId) {
                // إذا كانت بطاقة أو تحويل، نخصم من حساب البنك، وإلا من الخزينة النقدية
                if (in_array($request->payment_method, ['card', 'transfer'])) {
                    $targetTreasury = \App\Models\Treasury::where('type', 'bank')->first() ?? \App\Models\Treasury::first();
                } else {
                    $targetTreasury = \App\Models\Treasury::where('type', 'cash')->first() ?? \App\Models\Treasury::first();
                }
                $treasuryId = $targetTreasury ? $targetTreasury->id : null;
            }

            if ($treasuryId) {
                $treasury = \App\Models\Treasury::findOrFail($treasuryId);

                // تسجيل حركة سحب في جدول الحركات المالية
                \App\Models\TreasuryTransaction::create([
                    'treasury_id' => $treasury->id,
                    'type' => 'withdrawal', // سحب مقابل سداد مستحقات لمورد
                    'amount' => $request->amount,
                    'source_type' => 'supplier_payment',
                    'source_id' => $supplier->id,
                    'user_id' => Auth::id(),
                    'notes' => 'سداد دفعة للمورد: ' . $supplier->name,
                ]);

                // تحديث الرصيد الحالي للخزينة أو البنك فوراً (خصم المبلغ)
                $treasury->current_balance -= $request->amount;
                $treasury->save();
            }
        });

        return redirect()->back()->with('success', 'تم تسجيل السداد للمورد وتحديث الخزينة بنجاح.');
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
public function destroyTransaction($id)
{
    $transaction = Transaction::findOrFail($id);

    DB::transaction(function () use ($transaction) {
        $supplier = $transaction->accountable;

        if ($supplier) {
            // عكس تأثير العملية على الرصيد (إذا كانت دفعة سداد نقص بها الرصيد، فعند الحذف يُعاد المبلغ للرصيد)
            // ملاحظة: يمكنك تغيير increment إلى decrement إذا كان اتجاه رصيد المورد عندك يعاكس ذلك
            $supplier->increment('balance', $transaction->amount);
        }

        $transaction->delete();
    });

    return redirect()->back()->with('success', 'تم حذف الإيصال بنجاح وتعديل رصيد المورد.');
}
}
