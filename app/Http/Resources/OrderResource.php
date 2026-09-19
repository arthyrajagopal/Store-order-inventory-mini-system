<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'customer' => [
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ],
            'items' => $this->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'tax_percentage' => (float) $item->tax_percentage,
                'line_subtotal' => (float) $item->line_subtotal,
                'line_tax' => (float) $item->line_tax,
                'line_total' => (float) $item->line_total,
            ]),
            'subtotal' => (float) $this->subtotal,
            'tax_total' => (float) $this->tax_total,
            'grand_total' => (float) $this->grand_total,
            'created_at' => $this->created_at,
        ];
    }
}
