<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Customer;

class SyncController extends Controller
{


public function getInitialData()
{
    try {
        $products = Product::with('branches')->get(); // أو جلب المنتجات حسب هيكلة جدولك
        $customers = Customer::all();

        return response()->json([
            'success' => true,
            'products' => $products,
            'customers' => $customers
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    public function syncInvoices(Request $request)
    {
        $request->validate([
            'invoices' => 'required|array',
            'invoices.*.uuid' => 'required|string|distinct',
            'invoices.*.total' => 'required|numeric',
            'invoices.*.items' => 'required|array',
        ]);

        $syncedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($request->invoices as $invoiceData) {
                $exists = Invoice::where('uuid', $invoiceData['uuid'])->exists();

                if (!$exists) {
Invoice::create([
    'uuid'           => $invoiceData['uuid'],
    'invoice_number' => $invoiceData['invoice_number'] ?? 'INV-' . strtoupper(substr(uniqid(), -6)),
    'branch_id'      => $request->user()->branch_id ?? 1,
    'customer_id'    => $invoiceData['customer_id'] ?? null,
    'shift_id'       => $invoiceData['shift_id'] ?? null,
    'total_amount'   => $invoiceData['total'] ?? $invoiceData['total_amount'],
    'paid_amount'    => $invoiceData['paid_amount'] ?? ($invoiceData['total'] ?? $invoiceData['total_amount']),
    'change_amount'  => $invoiceData['change_amount'] ?? 0,
    'payment_method' => $invoiceData['payment_method'] ?? 'cash',
    'is_synced'      => true,
    'synced_at'      => Carbon::now(),
]);

                    $syncedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "تمت مزامنة {$syncedCount} فاتورة بنجاح.",
                'data'    => [
                    'synced_count' => $syncedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'فشلت عملية المزامنة بسبب خطأ في الخادم.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}