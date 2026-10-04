<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'branch_id',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method'
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}