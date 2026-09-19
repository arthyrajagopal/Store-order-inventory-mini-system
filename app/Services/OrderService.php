<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order for the given payload:
     * ['customer_email' => ..., 'customer_name' => ..., 'items' => [['product_id'=>, 'quantity'=>], ...]]
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::firstOrCreate(
                ['email' => $data['customer_email']],
                ['name' => $data['customer_name'] ?? $data['customer_email']]
            );

            $subtotal = 0;
            $taxTotal = 0;
            $lineData = [];

            // Sort by product_id before locking so two concurrent orders
            // that share multiple products always acquire row locks in the
            // same order — this avoids a classic deadlock.
            $items = collect($data['items'])->sortBy('product_id')->values();

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = (int) $item['quantity'];

                // Row-level lock: no other transaction can read/modify this
                // product row (via lockForUpdate or the atomic UPDATE below)
                // until we commit or roll back.
                $product = Product::where('id', $productId)->lockForUpdate()->first();

                if (! $product || $product->stock_on_hand < $quantity) {
                    throw new InsufficientStockException(
                        productId: $productId,
                        productName: $product->name ?? "#{$productId}",
                        requested: $quantity,
                        available: $product->stock_on_hand ?? 0,
                    );
                }

                // Atomic, conditional decrement as a second line of defence:
                // even without lockForUpdate support (or on isolation levels
                // where the lock is weaker), this UPDATE...WHERE only
                // succeeds if enough stock is still present at write time.
                $affected = DB::table('products')
                    ->where('id', $productId)
                    ->where('stock_on_hand', '>=', $quantity)
                    ->decrement('stock_on_hand', $quantity);

                if ($affected === 0) {
                    throw new InsufficientStockException(
                        productId: $productId,
                        productName: $product->name,
                        requested: $quantity,
                        available: $product->fresh()->stock_on_hand,
                    );
                }

                $unitPrice = (float) $product->price_per_unit;
                $taxPct = (float) $product->tax_percentage;

                $lineSubtotal = round($unitPrice * $quantity, 2);
                $lineTax = round($lineSubtotal * ($taxPct / 100), 2);
                $lineTotal = round($lineSubtotal + $lineTax, 2);

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;

                $lineData[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxPct,
                    'line_subtotal' => $lineSubtotal,
                    'line_tax' => $lineTax,
                    'line_total' => $lineTotal,
                ];
            }

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'grand_total' => round($subtotal + $taxTotal, 2),
                'status' => 'completed',
            ]);

            foreach ($lineData as $line) {
                $order->items()->create($line);
            }

            // Dispatch after the transaction commits so we never queue a
            // confirmation for an order that ends up rolled back.
            DB::afterCommit(fn () => SendOrderConfirmationEmail::dispatch($order->id));

            return $order->load(['items.product', 'customer']);
        });
    }
}
