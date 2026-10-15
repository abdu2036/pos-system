<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class PurchaseInvoiceController extends Controller implements HasMiddleware
{
        public static function middleware(): array
    {
        return [
            new Middleware('permission:manage purchase invoices', only: ['index']),
            new Middleware('permission:create purchase invoices', only: ['create', 'store']),
            new Middleware('permission:edit purchase invoices', only: ['edit', 'update']),
            new Middleware('permission:delete purchase invoices', only: ['destroy']),
             new Middleware('permission:print purchase invoices', only: ['print']),
              new Middleware('permission:print supplier receipt', only: ['printSupplierReceipt']),
              new Middleware('permission:settle purchase invoices', only: ['settle']),
        ];
    }
public function index(Request $request)
{
    $search = $request->input('search');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $query = PurchaseInvoice::with('supplier')
        ->when($search, function ($query, $search) {
            return $query->where('invoice_number', 'like', "%{$search}%")
                         ->orWhereHas('supplier', function ($q) use ($search) {
                             $q->where('name', 'like', "%{$search}%");
                         });
        })
        ->when($startDate, function ($query, $startDate) {
            return $query->whereDate('created_at', '>=', $startDate);
        })
        ->when($endDate, function ($query, $endDate) {
            return $query->whereDate('created_at', '<=', $endDate);
        });

    // حساب الإجماليات والكروت بناءً على نتائج البحث والفلترة الزمنية
    $statsQuery = clone $query;
    $totalInvoices = $statsQuery->count();
    $totalAmount = $statsQuery->sum('total_amount');
    $totalPaid = $statsQuery->sum('paid_amount');
    
    // ربط إجمالي المتبقي (الديون) ليطابق أرصدة الموردين الحالية تماماً
    $totalRemaining = Supplier::sum('balance');

    // فحص إذا كان المطلوب طباعة التقرير (مع الحفاظ على الفلترة)
    if ($request->has('print')) {
        $invoices = $query->latest()->get();
        $company = \App\Models\CompanySetting::first();
        
        // جلب بيانات الفرع للتقرير المطبوع
        $branch = \App\Models\Branch::first(); 

        return view('admin.purchase_invoices.print_report', compact(
            'invoices', 
            'totalInvoices', 
            'totalAmount', 
            'totalPaid', 
            'totalRemaining', 
            'company', 
            'branch', 
            'startDate', 
            'endDate'
        ));
    }

    $invoices = $query->latest()->paginate(10)->withQueryString();

    return view('admin.purchase_invoices.index', compact(
        'invoices', 
        'search', 
        'startDate',
        'endDate',
        'totalInvoices', 
        'totalAmount', 
        'totalPaid', 
        'totalRemaining'
    ));
}
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $categories = Category::all();
        $treasuries = Treasury::where('is_active', true)->get();

        return view('admin.purchase_invoices.create', compact('suppliers', 'products', 'categories', 'treasuries'));
    }

public function store(Request $request)
    {
        $request->validate([
            'supplier_id'            => 'required|exists:suppliers,id',
            'payment_method'         => 'required|string',
            'paid_amount'            => 'nullable|numeric|min:0',
            'treasury_id'            => 'nullable|exists:treasuries,id',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.selling_price'  => 'required|numeric|min:0',
            'items.*.wholesale_price'=> 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['purchase_price'];
            }

            $paidAmount = in_array($request->payment_method, ['cash', 'نقداً']) && !$request->filled('paid_amount') 
                ? $totalAmount 
                : ($request->paid_amount ?? 0);

            $remainingAmount = $totalAmount - $paidAmount;

            // توليد رقم الفاتورة بالسنة والتسلسل التلقائي
            $year = date('Y');
            $latest = PurchaseInvoice::where('invoice_number', 'like', "PUR-{$year}-%")->latest('id')->first();
            $nextNumber = $latest ? intval(substr($latest->invoice_number, -4)) + 1 : 1;
            $invoiceNumber = 'PUR-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $invoiceNumber,
                'supplier_id'    => $request->supplier_id,
                'total_amount'   => $totalAmount,
                'paid_amount'    => $paidAmount,
                'payment_method' => $request->payment_method,
                'notes'          => $request->notes,
            ]);

            $mainBranch = \App\Models\Branch::first();

            foreach ($request->items as $itemData) {
                $subtotal = $itemData['quantity'] * $itemData['purchase_price'];

                // تسجيل الصنف في الفاتورة
                $invoice->items()->create([
                    'product_id'     => $itemData['product_id'],
                    'quantity'       => $itemData['quantity'],
                    'purchase_price' => $itemData['purchase_price'],
                    'subtotal'       => $subtotal,
                ]);

                $product = Product::find($itemData['product_id']);
                if ($product) {
                    // تحديث أسعار الشراء والبيع حسب ما حدده التاجر يدويًا
                    $product->update([
                        'cost_price'      => $itemData['purchase_price'],
                        'selling_price'   => $itemData['selling_price'],
                        'wholesale_price' => $itemData['wholesale_price'] ?? $product->wholesale_price,
                    ]);

                    // تحديث الكمية العامة للمنتج
                    $product->increment('quantity', $itemData['quantity']);

                    // تحديث الكمية الخاصة بالفرع
                    if ($mainBranch) {
                        $branchProduct = $product->branches()->where('branch_id', $mainBranch->id)->first();

                        if ($branchProduct) {
                            $product->branches()->updateExistingPivot($mainBranch->id, [
                                'quantity' => $branchProduct->pivot->quantity + $itemData['quantity']
                            ]);
                        } else {
                            $product->branches()->attach($mainBranch->id, [
                                'quantity'       => $itemData['quantity'],
                                'alert_quantity' => 5
                            ]);
                        }
                    }
                }
            }

            // 1. إذا كان هناك مبلغ متبقي (آجل)، يُضاف إلى رصيد المورد
            if ($remainingAmount > 0) {
                $supplier = Supplier::find($request->supplier_id);
                if ($supplier) {
                    $supplier->increment('balance', $remainingAmount);
                }
            }

            // 2. إذا تم دفع مبلغ، يُخصم من الخزينة أو البنك ويسجل كحركة سحب
            if ($paidAmount > 0) {
                $treasuryId = $request->treasury_id;
                if (!$treasuryId) {
                    if (in_array($request->payment_method, ['card', 'transfer'])) {
                        $targetTreasury = Treasury::where('type', 'bank')->first() ?? Treasury::first();
                    } else {
                        $targetTreasury = Treasury::where('type', 'cash')->first() ?? Treasury::first();
                    }
                    $treasuryId = $targetTreasury ? $targetTreasury->id : null;
                }

                if ($treasuryId) {
                    $treasury = Treasury::findOrFail($treasuryId);

                    TreasuryTransaction::create([
                        'treasury_id' => $treasury->id,
                        'type'        => 'withdrawal',
                        'amount'      => $paidAmount,
                        'source_type' => 'purchase',
                        'source_id'   => $invoice->id,
                        'user_id'     => Auth::id(),
                        'notes'       => 'سداد فاتورة شراء رقم: ' . $invoice->invoice_number,
                    ]);

                    $treasury->current_balance -= $paidAmount;
                    $treasury->save();
                }
            }
        });

        return redirect()->route('purchase_invoices.index')
            ->with('success', 'تم حفظ فاتورة الشراء وتحديث الأسعار والمخزون والخزينة بنجاح.');
    }

public function storeQuickProduct(Request $request)
{
    // دمج سعر الشراء بغض النظر عن الاسم المرسل (purchase_price أو cost_price)
    $purchasePrice = $request->input('purchase_price') ?? $request->input('cost_price');

    $validated = $request->validate([
        'name'            => 'required|string|max:255|unique:products,name',
        'barcode'         => 'nullable|string|max:255|unique:products,barcode',
        'category_id'     => 'required|exists:categories,id',
        'unit'            => 'required|string',
        'selling_price'   => 'required|numeric|min:0',
        'wholesale_price' => 'nullable|numeric|min:0',
    ], [
        'name.required'        => 'اسم المنتج مطلوب',
        'name.unique'          => 'اسم المنتج مضاف مسبقاً!',
        'barcode.unique'       => 'الباركود مستخدم لمنتج آخر!',
        'category_id.required' => 'يرجى اختيار التصنيف',
    ]);

    // التأكد من وجود قيمة لسعر الشراء
    if (is_null($purchasePrice)) {
        return response()->json([
            'errors' => ['purchase_price' => ['حقل سعر الشراء مطلوب']]
        ], 422);
    }

    $product = Product::create([
        'name'            => $validated['name'],
        'barcode'         => $validated['barcode'] ?? null,
        'category_id'     => $validated['category_id'],
        'unit'            => $validated['unit'],
        'cost_price'      => $purchasePrice,
        'selling_price'   => $validated['selling_price'],
        'wholesale_price' => $validated['wholesale_price'] ?? 0,
    ]);

    $mainBranch = Branch::first();
    if ($mainBranch) {
        $product->branches()->attach($mainBranch->id, [
            'quantity'       => 0,
            'alert_quantity' => 5,
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'تم إضافة المنتج بنجاح',
        'product' => [
            'id'             => $product->id,
            'name'           => $product->name,
            'barcode'        => $product->barcode,
            'cost_price'     => $product->cost_price,
            'selling_price'  => $product->selling_price,
            'wholesale_price'=> $product->wholesale_price,
            'unit'           => $product->unit,
        ]
    ], 200);
}
    public function edit($id)
    {
        $purchaseInvoice = PurchaseInvoice::with(['items', 'supplier'])->findOrFail($id);
        $suppliers = Supplier::all();
        $products = Product::all();
        $categories = Category::all();

        return view('admin.purchase_invoices.edit', compact(
            'purchaseInvoice',
            'suppliers',
            'products',
            'categories'
        ));
    }

  public function update(Request $request, $id)
{
    $invoice = PurchaseInvoice::findOrFail($id);

    // 1. قواعد التحقق
    $request->validate([
        'supplier_id'            => 'required|exists:suppliers,id',
        'payment_method'         => 'required|string',
        'items'                  => 'required|array|min:1',
        'items.*.product_id'     => 'required|exists:products,id',
        'items.*.quantity'       => 'required|numeric|min:0.01',
        'items.*.purchase_price' => 'required|numeric|min:0',
    ]);

    DB::transaction(function () use ($request, $invoice) {
        $totalAmount = 0;

        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['purchase_price'];
        }

        // تحديد المبلغ المدفوع الصحيح:
        $paidAmount = in_array($request->payment_method, ['cash', 'نقداً']) 
            ? $totalAmount 
            : ($request->filled('paid_amount') ? $request->paid_amount : $invoice->paid_amount);

        // 2. تحديث بيانات الفاتورة الرئيسية
        $invoice->update([
            'supplier_id'    => $request->supplier_id,
            'payment_method' => $request->payment_method,
            'total_amount'   => $totalAmount,
            'paid_amount'    => $paidAmount,
            'notes'          => $request->notes,
        ]);

        // 3. مسح العناصر القديمة
        $invoice->items()->delete();

        // 4. إعادة إدخال العناصر الجديدة
        foreach ($request->items as $item) {
            $price = $item['purchase_price'];
            $quantity = $item['quantity'];

            $invoice->items()->create([
                'product_id'     => $item['product_id'],
                'quantity'       => $quantity,
                'purchase_price' => $price,
                'subtotal'       => $quantity * $price,
            ]);

            // 5. تحديث أسعار المنتجات
            $productUpdate = ['cost_price' => $price];

            if (!empty($item['selling_price'])) {
                $productUpdate['selling_price'] = $item['selling_price'];
            }
            if (!empty($item['wholesale_price'])) {
                $productUpdate['wholesale_price'] = $item['wholesale_price'];
            }

            Product::where('id', $item['product_id'])->update($productUpdate);
        }
    });

    return redirect()->route('purchase_invoices.index')->with('success', 'تم تعديل الفاتورة بنجاح.');
}

//
    public function settle(Request $request, $id) // دالة لتسوية حساب الفاتورة
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'treasury_id'    => 'nullable|exists:treasuries,id',
        ]);

        $invoice = PurchaseInvoice::findOrFail($id);

        $currentRemaining = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        $amountPaid = (float) $request->amount;

        if (round($amountPaid, 2) > round($currentRemaining, 2)) {
            return redirect()->back()->with('error', 'المبلغ المدفوع أكبر من المتبقي على الفاتورة.');
        }

        DB::transaction(function () use ($invoice, $amountPaid, $request) {
            $invoice->paid_amount += $amountPaid;
            $invoice->save();

            $supplier = $invoice->supplier;
            if ($supplier) {
                $supplier->decrement('balance', $amountPaid);

                $supplier->transactions()->create([
                    'type'           => 'payment',
                    'amount'         => $amountPaid,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'notes'          => 'تسوية فاتورة شراء رقم: ' . ($invoice->invoice_number ?? $invoice->id),
                ]);
            }

            // الخصم من الخزينة أو البنك عند التسوية
            $treasuryId = $request->treasury_id;
            if (!$treasuryId) {
                if (in_array($request->payment_method, ['card', 'transfer'])) {
                    $targetTreasury = Treasury::where('type', 'bank')->first() ?? Treasury::first();
                } else {
                    $targetTreasury = Treasury::where('type', 'cash')->first() ?? Treasury::first();
                }
                $treasuryId = $targetTreasury ? $targetTreasury->id : null;
            }

            if ($treasuryId) {
                $treasury = Treasury::findOrFail($treasuryId);

                TreasuryTransaction::create([
                    'treasury_id' => $treasury->id,
                    'type'        => 'withdrawal',
                    'amount'      => $amountPaid,
                    'source_type' => 'purchase_settlement',
                    'source_id'   => $invoice->id,
                    'user_id'     => Auth::id(),
                    'notes'       => 'تسوية وسداد فاتورة شراء رقم: ' . $invoice->invoice_number,
                ]);

                $treasury->current_balance -= $amountPaid;
                $treasury->save();
            }
        });

        return redirect()->back()->with('success', 'تم تسوية حساب الفاتورة وتحديث الخزينة بنجاح.');
    }

    public function destroy($id)
    {
        $invoice = PurchaseInvoice::with('items')->findOrFail($id);
        
        DB::transaction(function () use ($invoice) {
            // 1. عكس رصيد المورد إذا كان هناك مبلغ آجل
            $remainingAmount = $invoice->total_amount - $invoice->paid_amount;
            if ($remainingAmount > 0 && $invoice->supplier_id) {
                $supplier = Supplier::find($invoice->supplier_id);
                if ($supplier) {
                    $supplier->decrement('balance', $remainingAmount);
                }
            }

            // 2. عكس الحركة المالية من الخزينة واسترجاع المبلغ المدفوع إن وجد
            $transaction = TreasuryTransaction::where('source_type', 'purchase')
                ->where('source_id', $invoice->id)
                ->first();

            if ($transaction) {
                $treasury = Treasury::find($transaction->treasury_id);
                if ($treasury) {
                    $treasury->current_balance += $transaction->amount;
                    $treasury->save();
                }
                $transaction->delete();
            }

            // 3. خصم الكميات المضافة من المخزون
            $mainBranch = Branch::first();
            if ($mainBranch) {
                foreach ($invoice->items as $item) {
                    $product = Product::with('branches')->find($item->product_id);
                    if ($product) {
                        $product->decrement('quantity', $item->quantity);

                        $branchProduct = $product->branches->where('id', $mainBranch->id)->first();
                        if ($branchProduct) {
                            $product->branches()->updateExistingPivot($mainBranch->id, [
                                'quantity' => max(0, $branchProduct->pivot->quantity - $item->quantity)
                            ]);
                        }
                    }
                }
            }

            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route('purchase_invoices.index')->with('success', 'تم حذف الفاتورة وعكس تأثيرها على المخزون والموردين والخزينة بنجاح.');
    }

    public function print(PurchaseInvoice $purchaseInvoice)
{
    // تحميل العلاقات المطلوبة لطباعة تفاصيل الفاتورة والمورد والشركة
    $purchaseInvoice->load(['supplier', 'items.product']);
    $company = \App\Models\CompanySetting::first();

    return view('admin.purchase_invoices.print', compact('purchaseInvoice', 'company'));
}

/**
 * طباعة واصل السداد للمورد
 */
public function printSupplierReceipt($paymentId)
{
    $payment = \App\Models\Transaction::with('accountable')->findOrFail($paymentId);
    $company = \App\Models\CompanySetting::first();

    return view('admin.suppliers.receipt_print', compact('payment', 'company'));
}
}