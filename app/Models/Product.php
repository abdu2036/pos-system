<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'sku', 'barcode', 
        'cost_price', 'wholesale_price', 'selling_price', 'unit', 'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'product_branch')
                    ->withPivot('quantity', 'alert_quantity')
                    ->withTimestamps();
    }
}