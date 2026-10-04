<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $guarded = [];

    // علاقة الفرع بالشركة (الفرع ينتمي لشركة واحدة)
    public function company()
    {
        return $this->belongsTo(CompanySetting::class, 'company_id');
    }

    // علاقة الفرع بالمستخدمين
    public function users()
    {
        return $this->hasMany(User::class, 'branch_id');
    }
}