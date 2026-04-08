<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleOrderRequest;
use App\Http\Resources\LoadingOrderResource;
use App\Services\OrderService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

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

    public function getMyOrders(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $orders = $this->orderService->getOrdersByCurrentUser(10);

            return LoadingOrderResource::collection($orders);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'No orders found for the current user'], 404);
        }
    }

    public function scheduleOrder(ScheduleOrderRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $order = $this->orderService->scheduleOrder($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Ordem agendada com sucesso',
                //'data' => new LoadingOrderResource($order)
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ordem de carregamento não encontrada.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            Log::error("Erro ao agendar ordem: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao processar agendamento.'], 500);
        }
    }
}
