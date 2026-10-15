<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
   public function index(Request $request)
    {
        $query = Customer::query();

        // 1. البحث السريع باسم الزبون أو رقم الهاتف
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // حساب الإجماليات والكروت بناءً على نتائج البحث
        $statsQuery = clone $query;
        $totalCustomers = $statsQuery->count();
        $totalDebts = $statsQuery->sum('balance'); // إجمالي الديون الحالية
        $totalCreditLimit = $statsQuery->sum('credit_limit'); // إجمالي الحد الائتماني

        // جلب الزبائن مرتبة من الأحدث للأقدم مع التصفح (أو يمكنك استخدام get() إذا كنت لا تزال تفضلها)
        $customers = $query->latest()->paginate(15)->withQueryString();

        // فحص إذا كان المطلوب طباعة تقرير الزبائن والديون
        if ($request->has('print')) {
            $company = \App\Models\CompanySetting::first();
            return view('admin.customers.print_report', compact('customers', 'totalCustomers', 'totalDebts', 'totalCreditLimit', 'company'));
        }

        return view('admin.customers.index', compact(
            'customers', 
            'totalCustomers', 
            'totalDebts', 
            'totalCreditLimit'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers')->where(function ($query) use ($request) {
                    return $query->where('phone', $request->phone);
                }),
            ],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
        ], [
            'name.unique' => 'هذا الزبون موجود بالفعل بنفس رقم الهاتف!',
            'name.required' => 'اسم الزبون مطلوب',
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'credit_limit' => $validated['credit_limit'] ?? 0,
            'balance' => 0,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الزبون بنجاح',
                'customer' => $customer,
            ], 200);
        }

        return redirect()->back()->with('success', 'تم إضافة الزبون بنجاح');
    }

    // تسديد دفعة من الزبون مع حماية عدم تجاوز الدين القائم
  // تسديد دفعة من الزبون مع ربطها بالخزينة وتحديث الرصيد
    public function addPayment(Request $request, Customer $customer) 
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'treasury_id' => 'nullable|exists:treasuries,id', // اختيار الخزينة أو البنك إن وجد
            'notes' => 'nullable|string',
        ]);

        // منع التسديد في حال لم يكن على الزبون دين
        if ($customer->balance <= 0) {
            return redirect()->back()->with('error', 'لا يوجد دين قائم على هذا الزبون لتسديده.');
        }

        // منع تسديد مبلغ أكبر من الدين المتبقي
        if ($request->amount > $customer->balance) {
            return redirect()->back()->with('error', 'المبلغ المدخل (' . number_format($request->amount, 2) . ' د.ل) يتجاوز الدين المتبقي على الزبون (' . number_format($customer->balance, 2) . ' د.ل).');
        }

        DB::transaction(function () use ($customer, $request) {
            // 1. خصم المبلغ من رصيد دين الزبون
            $customer->decrement('balance', $request->amount);

            // 2. تسجيل حركة السداد في معاملات الزبون
            $transaction = $customer->transactions()->create([
                'type' => 'payment',
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes ?? 'سداد دفعة من الحساب'
            ]);

            // 3. التوجيه الذكي وتحديث الخزينة أو الحساب البنكي
            $treasuryId = $request->treasury_id;
            
            if (!$treasuryId) {
                // إذا كانت بطاقة أو تحويل، نختار حساب البنك تلقائياً، وإلا الخزينة النقدية
                if (in_array($request->payment_method, ['card', 'transfer'])) {
                    $targetTreasury = \App\Models\Treasury::where('type', 'bank')->first() ?? \App\Models\Treasury::first();
                } else {
                    $targetTreasury = \App\Models\Treasury::where('type', 'cash')->first() ?? \App\Models\Treasury::first();
                }
                $treasuryId = $targetTreasury ? $targetTreasury->id : null;
            }

            if ($treasuryId) {
                $treasury = \App\Models\Treasury::findOrFail($treasuryId);

                // تسجيل الحركة المالية في جدول خزائن الحركات
                \App\Models\TreasuryTransaction::create([
                    'treasury_id' => $treasury->id,
                    'type' => 'deposit', // إيداع نقدي/بنكي مقابل تحصيل دين من زبون
                    'amount' => $request->amount,
                    'source_type' => 'customer_payment',
                    'source_id' => $customer->id,
                    'user_id' => Auth::id(),
                    'notes' => 'تحصيل دفعة دين من الزبون: ' . $customer->name,
                ]);

                // تحديث الرصيد الحالي للخزينة أو البنك فوراً
                $treasury->current_balance += $request->amount;
                $treasury->save();
            }
        });

        return redirect()->back()->with('success', 'تم تسجيل السداد وتحديث رصيد الزبون والخزينة بنجاح.');
    }

    // عرض كشف حساب الزبون
    public function show(Customer $customer)
    {
        $transactions = $customer->transactions()->latest()->get();
        return view('admin.customers.show', compact('customer', 'transactions'));
    }

    // حذف واصل/معاملة مالية مع تعديل رصيد الزبون تلقائياً
    public function destroyTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);

        DB::transaction(function () use ($transaction) {
            if ($transaction->accountable_type === Customer::class || str_ends_with($transaction->accountable_type, 'Customer')) {
                $customer = $transaction->accountable;

                if ($customer) {
                    // إلغاء واصل سداد: إرجاع المبلغ إلى الدين
                    if ($transaction->type == 'payment' || $transaction->type == 'receipt') {
                        $customer->increment('balance', $transaction->amount);
                    } 
                    // إلغاء فاتورة: خصم المبلغ من الدين
                    elseif ($transaction->type == 'invoice' || $transaction->type == 'sale') {
                        $customer->decrement('balance', $transaction->amount);
                    }
                }
            }

            $transaction->delete();
        });

        return redirect()->back()->with('success', 'تم حذف المعاملة وتعديل رصيد الزبون بنجاح.');
    }

    // حذف الزبون
    public function destroy($id)
    {
        $customer = Customer::withCount(['salesInvoices', 'transactions'])->findOrFail($id);

        if ($customer->balance != 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف الزبون بسبب وجود رصيد/التزام مالي قائم عليه.');
        }

        if ($customer->sales_invoices_count > 0 || $customer->transactions_count > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف الزبون لوجود معاملات مالية أو فواتير سابقة مرتبطة به.');
        }

        $customer->delete();

        return redirect()->back()->with('success', 'تم حذف الزبون بنجاح.');
    }

    // تعديل بيانات الزبون
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'credit_limit' => $request->credit_limit ?? 0,
        ]);

        return redirect()->back()->with('success', 'تم تعديل بيانات الزبون بنجاح.');
    }

    // طباعة إيصال القبض
    public function printReceipt($id)
    {
        $transaction = Transaction::with('accountable')->findOrFail($id);
        $company = \App\Models\CompanySetting::first();

        return view('admin.customers.receipt_print', compact('transaction', 'company'));
    }
}