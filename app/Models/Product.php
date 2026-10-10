<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 
        'name', 
        'sku', 
        'barcode', 
        'cost_price',      // سعر الشراء/التكلفة
        'wholesale_price', // سعر الجملة
        'selling_price',   // سعر القطاعي
        'unit', 
        'quantity',
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