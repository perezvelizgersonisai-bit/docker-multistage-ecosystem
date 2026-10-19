<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $subtotal
 */
class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
