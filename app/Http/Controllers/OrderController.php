<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoadingOrderResource;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{

    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function getOrder($orderId = null): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|LoadingOrderResource
    {
        try {
            if ($orderId) {
                $order = $this->orderService->findOrderById($orderId);

                return new LoadingOrderResource($order);
            }

            $orders = $this->orderService->getAllOrders(10);

            return LoadingOrderResource::collection($orders);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Order not found'], 404);
        }

    }
}
