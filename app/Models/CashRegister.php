<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'opening_amount',
        'closing_amount',
        'status',
        'opened_at',
        'closed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function notes()
    {
        return $this->hasMany(CashRegisterNote::class);
    }
}
