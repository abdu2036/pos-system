<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    // علاقة الشركة بالفروع (الشركة لديها عدة فروع)
    public function branches()
    {
        return $this->hasMany(Branch::class, 'company_id');
    }
}