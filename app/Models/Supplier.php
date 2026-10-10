<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'company_name', 'phone', 'address', 'balance'];

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'accountable');
    }

    public function purchaseInvoices() 
{
    return $this->hasMany(PurchaseInvoice::class);
}
}