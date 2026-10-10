<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->get();
        return view('admin.customers.index', compact('customers'));
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
    public function addPayment(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
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
            // خصم المبلغ من رصيد دين الزبون
            $customer->decrement('balance', $request->amount);

            // تسجيل حركة السداد
            $customer->transactions()->create([
                'type' => 'receipt',
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes ?? 'سداد دفعة من الحساب'
            ]);
        });

        return redirect()->back()->with('success', 'تم تسجيل السداد وتحديث رصيد الزبون بنجاح.');
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