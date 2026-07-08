<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'variation_id', 'quantity', 'price', 'notes'];

    protected $casts = [
        'addons' => 'array',
    ];

    public function variation()
    {
        return $this->belongsTo(\App\Models\ProductVariation::class, 'variation_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function addons()
    {
        return $this->belongsToMany(Product::class, 'order_item_addons', 'order_item_id', 'addon_id')
                    ->withPivot('quantity', 'price');
    }
}
