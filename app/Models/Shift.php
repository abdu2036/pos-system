<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'treasury_id',
        'opening_balance',
        'total_sales',
        'total_cash_sales',
        'closing_balance',
        'expected_balance',
        'difference',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

   

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }
    public function branch()
{
    return $this->belongsTo(Branch::class);
}

public function invoices()
{
    return $this->hasMany(Invoice::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
}