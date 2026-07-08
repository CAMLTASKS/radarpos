<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'value',
        'free_product_id',
        'free_addon_id',
        'is_active'
    ];

    public function freeProduct()
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }

    public function freeAddon()
    {
        return $this->belongsTo(Addon::class, 'free_addon_id');
    }
}
