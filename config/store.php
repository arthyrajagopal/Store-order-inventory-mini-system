<?php

return [
    // Default low-stock threshold used by GET /api/products/low-stock
    // when no ?threshold= query param is supplied. Override via
    // STORE_LOW_STOCK_THRESHOLD in .env.
    'low_stock_threshold' => env('STORE_LOW_STOCK_THRESHOLD', 10),
];
