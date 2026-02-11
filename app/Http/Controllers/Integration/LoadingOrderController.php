<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\OrderIntegrationRequest;
use App\Services\LoadingOrderService;


class LoadingOrderController extends Controller
{
    public function __construct(private readonly LoadingOrderService $service){}

    /**
     * @throws \Exception
     */
    public function storeOrder(OrderIntegrationRequest $request): \Illuminate\Http\JsonResponse
    {
        $order = $this->service->storeOrder($request->validated());

        return response()->json([
            'message' => 'Payload received successfully',
            'data' => $order
        ], 201);
    }
}
