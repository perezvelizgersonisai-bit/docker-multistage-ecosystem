<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $order_id
 * @property string $stripe_payment_intent_id
 * @property float $amount
 * @property string $currency
 * @property string $status
 */
class Payment extends Model
{
    protected $fillable = ['order_id', 'stripe_payment_intent_id', 'amount', 'currency', 'status'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
