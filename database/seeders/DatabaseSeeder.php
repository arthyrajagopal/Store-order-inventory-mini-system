<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()->count(10)->create();

        Product::factory()->count(15)->create();

        // A few guaranteed low-stock products so the low-stock endpoint
        // has something to return out of the box.
        Product::factory()->count(3)->lowStock()->create();
    }
}
