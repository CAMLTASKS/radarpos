<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'unit', 'stock'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')->withPivot('quantity');
    }
}
