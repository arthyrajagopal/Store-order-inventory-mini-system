<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function lowStock(Request $request): JsonResponse
    {
        $request->validate([
            'threshold' => ['sometimes', 'integer', 'min:0'],
        ]);

        // Defaults to config('store.low_stock_threshold') if not passed,
        // so the "configurable" requirement works both via query param
        // and via a central app setting.
        $threshold = (int) $request->query(
            'threshold',
            config('store.low_stock_threshold', 10)
        );

        $products = Product::lowStock($threshold)
            ->orderBy('stock_on_hand')
            ->get(['id', 'name', 'code', 'stock_on_hand']);

        return response()->json([
            'threshold' => $threshold,
            'count' => $products->count(),
            'products' => $products,
        ]);
    }
}
