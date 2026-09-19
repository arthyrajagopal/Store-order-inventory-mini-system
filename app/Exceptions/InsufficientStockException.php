<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly int $productId,
        public readonly string $productName,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(
            "Insufficient stock for '{$productName}' (id {$productId}): requested {$requested}, available {$available}."
        );
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'product_id' => $this->productId,
                'requested' => $this->requested,
                'available' => $this->available,
            ],
        ], 409); // 409 Conflict — clean, expected failure, not a 500.
    }
}
