<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 500);
        $tax = round($subtotal * 0.18, 2);

        return [
            'customer_id' => Customer::factory(),
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'grand_total' => $subtotal + $tax,
            'status' => 'completed',
        ];
    }
}
