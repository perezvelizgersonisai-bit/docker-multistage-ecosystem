<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(2, true)),
            'description' => $this->faker->sentence(10),
            'price' => $this->faker->randomFloat(2, 5, 200),
            'stock' => $this->faker->numberBetween(0, 100),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####??')),
        ];
    }
}
