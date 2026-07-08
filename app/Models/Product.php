<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'image', 'price', 'category', 'is_addon'];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')->withPivot('quantity');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }
}
