<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::with(['customer', 'items.product'])->find($this->orderId);

        if (! $order) {
            Log::warning("SendOrderConfirmationEmail: order {$this->orderId} not found, skipping.");
            return;
        }

        // Stand-in for a real mailer. Swap this for
        // Mail::to($order->customer->email)->send(new OrderConfirmationMail($order));
        // once real SMTP/queue infra is available — no other code changes needed.
        Log::info('Order confirmation email sent (simulated)', [
            'order_id' => $order->id,
            'to' => $order->customer->email,
            'grand_total' => $order->grand_total,
            'items' => $order->items->count(),
        ]);
    }
}
