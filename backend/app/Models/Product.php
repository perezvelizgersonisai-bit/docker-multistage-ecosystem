<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property int $stock
 * @property string $sku
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'stock', 'sku'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
