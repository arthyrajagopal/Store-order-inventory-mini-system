<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function history(Request $request, string $email): JsonResponse
    {
        $request->validate(['email' => ['sometimes']]);

        $customer = Customer::where('email', $email)->first();

        if (! $customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        $orders = $customer->orders()
            ->with(['items.product'])
            ->latest()
            ->get();

        return OrderResource::collection($orders)->response();
    }
}
