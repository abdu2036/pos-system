<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Treasury;
use App\Models\CompanySetting;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class ShiftController extends Controller implements HasMiddleware
{
   public static function middleware(): array
    {
        return [
            new Middleware('permission:manage shifts', only: ['index']),
            new Middleware('permission:open shifts', only: ['create', 'store']),
            new Middleware('permission:show shifts', only: ['show']),
            new Middleware('permission:edit shifts', only: ['edit', 'update']),
            new Middleware('permission:close shifts', only: ['closeForm']),
        ];
    }

public function index(Request $request)
{
    $query = Shift::with(['user', 'branch', 'invoices.customer']);

    // تصفية حسب حالة الوردية (مفتوحة / مغلقة)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // تصفية حسب الموظف / الكاشير
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    // تصفية حسب الفرع
    if ($request->filled('branch_id')) {
        $query->where('branch_id', $request->branch_id);
    }

    // تصفية من تاريخ
    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    // تصفية إلى تاريخ
    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    $shifts = $query->latest()->paginate(10)->withQueryString();
    
    // جلب القوائم المنسدلة للبحث
    $users = \App\Models\User::all();
    $branches = \App\Models\Branch::all();

    return view('admin.shifts.index', compact('shifts', 'users', 'branches'));
}

public function show($id)
{
    $shift = Shift::with(['user'])->findOrFail($id);

    return view('admin.shifts.show', compact('shift'));
}
    // عرض صفحة فتح وردية جديدة
    public function create()
    {
        $company = CompanySetting::first();
        if (!$company || !$company->enable_shifts) {
            return redirect()->route('admin.dashboard')->with('error', 'نظام الورديات غير مفعل في إعدادات الشركة.');
        }

        // التحقق إن كانت هناك وردية مفتوحة للمستخدم الحالي
        $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();
        if ($activeShift) {
            return redirect()->back()->with('info', 'لديك وردية مفتوحة بالفعل.');
        }

        return view('admin.shifts.create');
    }

    // تخزين وردية جديدة (فتح الوردية) تلقائياً بصندوق النقدية الرئيسي
public function store(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        // منع فتح أكثر من وردية مفتوحة لنفس المستخدم
        $activeShift = Shift::where('user_id', $user->id)->where('status', 'open')->first();
        if ($activeShift) {
            return redirect()->back()->with('error', 'لديك وردية مفتوحة بالفعل ولا يمكن فتح وردية أخرى.');
        }

        // جلب صندوق النقدية (Cash) الخاص بفرع المستخدم الحالي
      // جلب صندوق النقدية (Cash) المتاح مباشرة
        $defaultCashTreasury = Treasury::where('type', 'cash')->first() ?? Treasury::first();

        if (!$defaultCashTreasury) {
            return redirect()->back()->with('error', 'لا يوجد صندوق نقدية (Cash) معرف في النظام. يرجى إضافته من إدارة الخزائن.');
        }

        Shift::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id, // ربط الوردية بالفرع أيضاً لضمان التقارير المستقلة
            'treasury_id' => $defaultCashTreasury->id,
            'opening_balance' => $request->opening_balance,
            'status' => 'open',
            'opened_at' => Carbon::now(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('treasuries.index')->with('success', 'تم فتح الوردية للفرع بنجاح وبدء العمل.');
    }
    // عرض شاشة الجرد وإغلاق الوردية
public function closeForm($id)
    {
        $shift = Shift::findOrFail($id);

        // المبيعات النقدية التي تدخل الدرج
        $cashSales = TreasuryTransaction::where('treasury_id', $shift->treasury_id)
            ->where('user_id', $shift->user_id)
            ->where('created_at', '>=', $shift->opened_at)
            ->where('type', 'deposit')
            ->sum('amount');

        // مبيعات البطاقات المصرفية (للعلم والإحاطة فقط ولا تدخل في جرد الدرج)
        $bankTreasury = Treasury::where('type', 'bank')->first();
        $cardSales = 0;
        if ($bankTreasury) {
            $cardSales = TreasuryTransaction::where('treasury_id', $bankTreasury->id)
                ->where('user_id', $shift->user_id)
                ->where('created_at', '>=', $shift->opened_at)
                ->where('type', 'deposit')
                ->sum('amount');
        }

        $expectedBalance = $shift->opening_balance + $cashSales;

        return view('admin.shifts.close', compact('shift', 'cashSales', 'cardSales', 'expectedBalance'));
    }
    // تنفيذ عملية إغلاق الوردية والجرد
    public function close(Request $request, $id)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift = Shift::findOrFail($id);

        // إعادة حساب المبيعات النقدية لفترة الوردية بدقة
        $cashSales = TreasuryTransaction::where('treasury_id', $shift->treasury_id)
            ->where('user_id', $shift->user_id)
            ->where('created_at', '>=', $shift->opened_at)
            ->where('type', 'deposit')
            ->sum('amount');

        $expectedBalance = $shift->opening_balance + $cashSales;
        $closingBalance = $request->closing_balance;
        $difference = $closingBalance - $expectedBalance; // إذا كان بالسالب = عجز، بالموجب = زيادة، 0 = متطابق تماماً

        $shift->update([
            'total_cash_sales' => $cashSales,
            'total_sales' => $cashSales,
            'expected_balance' => $expectedBalance,
            'closing_balance' => $closingBalance,
            'difference' => $difference,
            'status' => 'closed',
            'closed_at' => Carbon::now(),
            'notes' => $request->notes,
        ]);
return redirect()->back()->with('success', 'تم إغلاق الوردية وجردها بنجاح.');
  }

  public function edit($id)
    {
        $shift = Shift::findOrFail($id);
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'closing_balance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shift = Shift::findOrFail($id);
        
        $shift->opening_balance = $request->opening_balance;
        
        if ($shift->status == 'closed') {
            $shift->closing_balance = $request->closing_balance ?? $shift->closing_balance;
            // إعادة حساب المبلغ المتوقع والفرق بدقة
            $expected = $shift->expected_balance ?? ($shift->opening_balance + ($shift->total_sales ?? 0));
            $shift->difference = $shift->closing_balance - $expected;
        }
        
        $shift->notes = $request->notes;
        $shift->save();

        return redirect()->route('shifts.index')->with('success', 'تم تحديث بيانات الوردية بنجاح.');
    }


}