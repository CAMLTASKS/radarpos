<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'cash_register_id', 'customer_id', 'customer_name', 'customer_phone', 
        'delivery_address', 'delivery_driver', 'delivery_type', 'payment_method', 
        'subtotal', 'delivery_fee', 'total', 'status', 'whatsapp_sent',
        'rating', 'feedback_comments', 'cancellation_reason', 'promotion_id', 'delivered_at', 'customer_confirmed_at'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
