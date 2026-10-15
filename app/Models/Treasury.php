<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treasury extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'opening_balance',
        'current_balance',
        'is_active',
        'notes',
    ];
// علاقة الخزينة بالحركات المالية المرتبطة بها
    public function transactions()
{
    return $this->hasMany(TreasuryTransaction::class);
}

}
