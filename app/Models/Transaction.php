<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'supplier_id',          // تم إضافة المورد
        'purchase_invoice_id',  // تم إضافة فاتورة الشراء
        'type',
        'amount',
        'payment_method',
        'notes',
    ];

    /**
     * علاقة المعاملة بالزبون
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * علاقة المعاملة بالمورد
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * علاقة المعاملة بفاتورة الشراء
     */
    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    // علاقة المعاملة بالكيان المسؤول (زبون أو مورد)
    public function accountable()
    {
        return $this->morphTo();
    }
}