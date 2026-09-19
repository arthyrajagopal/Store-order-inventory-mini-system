<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'code' => strtoupper($this->faker->unique()->bothify('SKU-####')),
            'price_per_unit' => $this->faker->randomFloat(2, 10, 500),
            'tax_percentage' => $this->faker->randomElement([0, 5, 12, 18]),
            'stock_on_hand' => $this->faker->numberBetween(0, 100),
        ];
    }

    /** Convenience state for low-stock testing/demo. */
    public function lowStock(int $qty = 3): static
    {
        return $this->state(fn () => ['stock_on_hand' => $qty]);
    }
}
