<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a customer order history by email', function () {
    $customer = Customer::factory()->create(['email' => 'history@example.com']);
    Order::factory()->count(2)->for($customer)->create();

    $response = $this->getJson('/api/customers/history@example.com/orders');

    $response->assertOk();
    $response->assertJsonCount(2);
});

it('returns 404 for an unknown customer email', function () {
    $this->getJson('/api/customers/nobody@example.com/orders')
        ->assertNotFound();
});

it('returns only products below the given low-stock threshold', function () {
    Product::factory()->create(['stock_on_hand' => 2]);
    Product::factory()->create(['stock_on_hand' => 50]);

    $response = $this->getJson('/api/products/low-stock?threshold=5');

    $response->assertOk();
    $response->assertJsonPath('count', 1);
});

it('falls back to the configured default threshold when none is given', function () {
    config(['store.low_stock_threshold' => 3]);
    Product::factory()->create(['stock_on_hand' => 1]);
    Product::factory()->create(['stock_on_hand' => 20]);

    $response = $this->getJson('/api/products/low-stock');

    $response->assertOk();
    $response->assertJsonPath('threshold', 3);
    $response->assertJsonPath('count', 1);
});
