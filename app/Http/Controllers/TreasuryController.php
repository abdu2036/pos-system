<?php

namespace App\Http\Controllers;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreasuryController extends Controller
{
    // عرض قائمة الخزائن والحسابات
  public function index(Request $request)
{
    $treasuries = Treasury::all();
    
    $query = TreasuryTransaction::with(['treasury', 'user'])
        ->when($request->filled('treasury_id'), function ($q) use ($request) {
            $q->where('treasury_id', $request->treasury_id);
        })
        ->when($request->filled('type'), function ($q) use ($request) {
            $q->where('type', $request->type);
        })
        ->when($request->filled('start_date'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->start_date);
        })
        ->when($request->filled('end_date'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->end_date);
        });

    // حساب الإحصائيات والكروت بناءً على نتائج البحث والفلترة
    $statsQuery = clone $query;
    $totalDeposits = (clone $statsQuery)->where('type', 'deposit')->sum('amount');
    $totalWithdrawals = (clone $statsQuery)->where('type', 'withdrawal')->sum('amount');
    $netCashFlow = $totalDeposits - $totalWithdrawals;
    $totalTreasuryBalance = Treasury::sum('current_balance');

    // فحص طلب الطباعة
    if ($request->has('print')) {
        $transactions = $query->latest()->get();
        $company = \App\Models\CompanySetting::first();
        return view('admin.treasuries.print_report', compact(
            'transactions', 'totalDeposits', 'totalWithdrawals', 'netCashFlow', 'totalTreasuryBalance', 'company'
        ));
    }

    $transactions = $query->latest()->paginate(10)->withQueryString();

    return view('admin.treasuries.index', compact(
        'treasuries', 'transactions', 'totalDeposits', 'totalWithdrawals', 'netCashFlow', 'totalTreasuryBalance'
    ));
}

    // تخزين خزينة جديدة وتحديد رصيد أول المدة كحركة مالية
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:cash,bank',
            'opening_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // 1. إنشاء الخزينة
            $treasury = Treasury::create([
                'name'            => $request->name,
                'type'            => $request->type,
                'opening_balance' => $request->opening_balance,
                'current_balance' => $request->opening_balance, // الرصيد الحالي يبدأ برصيد أول المدة
                'is_active'       => true,
                'notes'           => $request->notes,
            ]);

            // 2. تسجيل رصيد أول المدة كحركة إيداع افتتاحية في جدول الحركات المالية
            if ($request->opening_balance > 0) {
                TreasuryTransaction::create([
                    'treasury_id' => $treasury->id,
                    'type'        => 'deposit',
                    'amount'      => $request->opening_balance,
                    'source_type' => 'opening_balance',
                    'source_id'   => $treasury->id,
                    'user_id'     => Auth::id(),
                    'notes'       => 'إثبات رصيد أول المدة للخزينة',
                ]);
            }
        });

        return redirect()->back()->with('success', 'تم إنشاء الخزينة وتحديد رصيد أول المدة وتسجيل الحركة المالية بنجاح.');
    }

    // تحديث بيانات الخزينة ورصيدها
    public function update(Request $request, $id)
    {
        $treasury = Treasury::findOrFail($id);

        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:cash,bank',
            'opening_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $treasury) {
            // حساب الفرق في رصيد أول المدة لتحديث الرصيد الحالي
            $diff = $request->opening_balance - $treasury->opening_balance;
            $newCurrentBalance = $treasury->current_balance + $diff;

            $treasury->update([
                'name'            => $request->name,
                'type'            => $request->type,
                'opening_balance' => $request->opening_balance,
                'current_balance' => $newCurrentBalance,
                'notes'           => $request->notes,
            ]);
        });

        return redirect()->back()->with('success', 'تم تحديث بيانات الخزينة ورصيدها بنجاح.');
    }

    // حذف الخزينة
    public function destroy($id)
    {
        $treasury = Treasury::findOrFail($id);
        
        // حذف الخزينة (سيتم حذف الحركات التابعة لها تلقائياً بفضل الـ onDelete('cascade') في الـ Migration)
        $treasury->delete();

        return redirect()->back()->with('success', 'تم حذف الخزينة بنجاح.');
    }

    /**
     * 💡 دالة مركزية مساعدة لربط أي عملية (بيع، شراء، مصروفات، سندات) بالخزينة وتحديث الرصيد لحظياً
     * 
     * @param int $treasuryId معرف الخزينة
     * @param string $type نوع الحركة ('deposit' إيداع أو 'withdrawal' سحب)
     * @param float $amount المبلغ
     * @param string $sourceType مصدر الحركة ('sale', 'purchase', 'expense', 'customer_payment'...)
     * @param int $sourceId معرف المستند أو الفاتورة المرتبطة
     * @param string|null $notes ملاحظات إضافية
     */
    public function addTransaction($treasuryId, $type, $amount, $sourceType, $sourceId, $notes = null)
    {
        DB::transaction(function () use ($treasuryId, $type, $amount, $sourceType, $sourceId, $notes) {
            $treasury = Treasury::findOrFail($treasuryId);

            // 1. تسجيل الحركة في جدول الحركات المالية
            TreasuryTransaction::create([
                'treasury_id' => $treasuryId,
                'type'        => $type,
                'amount'      => $amount,
                'source_type' => $sourceType,
                'source_id'   => $sourceId,
                'user_id'     => Auth::id(),
                'notes'       => $notes,
            ]);

            // 2. تحديث الرصيد الحالي للخزينة فوراً
            if ($type == 'deposit') {
                $treasury->current_balance += $amount;
            } else {
                $treasury->current_balance -= $amount;
            }

            $treasury->save();
        });
    }

    // عرض تقرير الحركة المالية للخزائن والحسابات مع الفلترة والطباعة
    public function report(Request $request)
    {
        $treasuries = Treasury::all();
        
        $query = TreasuryTransaction::with(['treasury', 'user'])
            ->when($request->filled('treasury_id'), function ($q) use ($request) {
                $q->where('treasury_id', $request->treasury_id);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            });

        // حساب الإحصائيات والكروت بناءً على نتائج البحث والفلترة
        $statsQuery = clone $query;
        $totalDeposits = (clone $statsQuery)->where('type', 'deposit')->sum('amount');
        $totalWithdrawals = (clone $statsQuery)->where('type', 'withdrawal')->sum('amount');
        $netCashFlow = $totalDeposits - $totalWithdrawals;
        $totalTreasuryBalance = Treasury::sum('current_balance');

        // فحص طلب الطباعة
        if ($request->has('print')) {
            $transactions = $query->latest()->get();
            $company = \App\Models\CompanySetting::first();
            return view('admin.treasuries.print_report', compact(
                'transactions', 'totalDeposits', 'totalWithdrawals', 'netCashFlow', 'totalTreasuryBalance', 'company'
            ));
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.treasuries.report', compact(
            'treasuries', 'transactions', 'totalDeposits', 'totalWithdrawals', 'netCashFlow', 'totalTreasuryBalance'
        ));
    }
}