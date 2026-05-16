<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_order_id',
        'menu_id',
        'qty',
        'unit_price',
        'subtotal',
    ];

    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
