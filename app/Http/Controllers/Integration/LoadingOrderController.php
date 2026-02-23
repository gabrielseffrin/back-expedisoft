<?php

namespace App\Http\Controllers\Integration;

use App\Exceptions\IntegrationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\OrderIntegrationRequest;
use App\Services\LoadingOrderService;


class LoadingOrderController extends Controller
{
    public function __construct(private readonly LoadingOrderService $service)
    {
    }

    /**
     * @throws \Exception
     */
    public function storeOrder(OrderIntegrationRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $order = $this->service->storeOrder($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payload received successfully',
                'data' => $order
            ], 201);

        } catch (IntegrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], $e->getHttpStatus());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }

    }
}
