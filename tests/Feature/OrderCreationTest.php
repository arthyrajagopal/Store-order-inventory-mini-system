<?php

use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('creates an order, computes totals correctly, and deducts stock', function () {
    Queue::fake();

    $product = Product::factory()->create([
        'price_per_unit' => 100,
        'tax_percentage' => 18,
        'stock_on_hand' => 10,
    ]);

    $response = $this->postJson('/api/orders', [
        'customer_email' => 'jane@example.com',
        'customer_name' => 'Jane Doe',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 3],
        ],
    ]);

    $response->assertCreated();
$response->assertJsonPath('subtotal', 300);
$response->assertJsonPath('tax_total', 54);
$response->assertJsonPath('grand_total', 354);

    $this->assertDatabaseHas('customers', ['email' => 'jane@example.com']);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_on_hand' => 7]);
    $this->assertDatabaseCount('orders', 1);
    $this->assertDatabaseCount('order_items', 1);

    Queue::assertPushed(SendOrderConfirmationEmail::class);
});

it('reuses an existing customer by email instead of duplicating', function () {
    Queue::fake();

    $customer = Customer::factory()->create(['email' => 'existing@example.com']);
    $product = Product::factory()->create(['stock_on_hand' => 5]);

    $this->postJson('/api/orders', [
        'customer_email' => $customer->email,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertCreated();

    $this->assertDatabaseCount('customers', 1);
});

it('rejects an order with no items', function () {
    $this->postJson('/api/orders', [
        'customer_email' => 'jane@example.com',
        'customer_name' => 'Jane',
        'items' => [],
    ])->assertStatus(422)->assertJsonValidationErrors('items');
});

it('rejects an order referencing a non-existent product', function () {
    $this->postJson('/api/orders', [
        'customer_email' => 'jane@example.com',
        'customer_name' => 'Jane',
        'items' => [['product_id' => 99999, 'quantity' => 1]],
    ])->assertStatus(422)->assertJsonValidationErrors('items.0.product_id');
});

// --- Edge case required by the brief ---
it('fails cleanly and does not deduct stock when quantity exceeds availability', function () {
    Queue::fake();

    $product = Product::factory()->create(['stock_on_hand' => 2]);

    $response = $this->postJson('/api/orders', [
        'customer_email' => 'jane@example.com',
        'customer_name' => 'Jane',
        'items' => [['product_id' => $product->id, 'quantity' => 5]],
    ]);

    $response->assertStatus(409);

    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_on_hand' => 2]);
    $this->assertDatabaseCount('orders', 0);
    Queue::assertNotPushed(SendOrderConfirmationEmail::class);
});

it('rolls back the whole order if any single line has insufficient stock', function () {
    Queue::fake();

    $inStock = Product::factory()->create(['stock_on_hand' => 10]);
    $outOfStock = Product::factory()->create(['stock_on_hand' => 1]);

    $this->postJson('/api/orders', [
        'customer_email' => 'jane@example.com',
        'customer_name' => 'Jane',
        'items' => [
            ['product_id' => $inStock->id, 'quantity' => 2],
            ['product_id' => $outOfStock->id, 'quantity' => 5],
        ],
    ])->assertStatus(409);

    // The in-stock product's stock must NOT have been deducted, since the
    // whole order failed.
    $this->assertDatabaseHas('products', ['id' => $inStock->id, 'stock_on_hand' => 10]);
    $this->assertDatabaseCount('orders', 0);
});
