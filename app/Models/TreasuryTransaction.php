<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'treasury_id',
        'type',
        'amount',
        'source_type',
        'source_id',
        'user_id',
        'notes',
    ];

    // علاقة الحركة بالخزينة
    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    // علاقة الحركة بالمستخدم الذي أجرها
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}