<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'balance', 'credit_limit'];

    /**
     * المعاملات المالية المرتبطة بالزبون (قبض/صرف/تسديد)
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'accountable');
    }

    /**
     * فواتير المبيعات المرتبطة بالزبون
     */
    public function salesInvoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    /**
     * التحقق مما إذا كان الزبون لديه أي التزامات أو معاملات مالية لتقييد الحذف
     */
    public function hasFinancialRecords(): bool
    {
        return $this->balance != 0 
            || $this->transactions()->exists() 
            || $this->salesInvoices()->exists();
    }
}