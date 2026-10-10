<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'branch_id',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
    ];

    /**
     * علاقة الفاتورة بالزبون
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * علاقة الفاتورة بالفرع
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * علاقة الفاتورة بعناصر الفاتورة (المنتجات المباعة)
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}