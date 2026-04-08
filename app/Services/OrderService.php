<?php

namespace App\Services;

use App\Models\LoadingOrder;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function findOrderById(string $orderId): LoadingOrder
    {
        return LoadingOrder::with('customer', 'destination', 'carrier', 'driver', 'vehicle', 'operator', 'dock', 'items.product', 'items.packages')->findOrFail($orderId);
    }

    public function getAllOrders(string $perPage): LengthAwarePaginator
    {
        return LoadingOrder::with('customer', 'destination', 'carrier', 'driver', 'vehicle', 'operator', 'dock', 'items.product', 'items.packages')->paginate($perPage);
    }

    public function getOrdersByCurrentUser(string $perPage): LengthAwarePaginator
    {
        $authUser = auth()->user();

        return LoadingOrder::with('customer', 'destination', 'carrier', 'driver', 'vehicle', 'operator', 'dock', 'items.product', 'items.packages')
            ->where('created_by', $authUser->id)
            ->orWhere('operator_id', $authUser->id)
            //->where('status', '!=', 'pending')
            ->orderBy('scheduled_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @throws \Exception
     */
    public function scheduleOrder(array $payload): LoadingOrder
    {

        $order = LoadingOrder::query()->findOrFail($payload['id']);
        $authUser = auth()->user();

        return DB::transaction(function () use ($order, $payload, $authUser) {

            if (isset($payload['operator_id'])) {
                $targetOperator = User::query()->findOrFail($payload['operator_id']);

                if ($targetOperator->rule !== 'operador') {
                    throw new AuthorizationException('O usuário selecionado não possui permissão de operador.');
                }
            }

            $oldStatus = $order->status;

            $order->update([
                'scheduled_at' => $payload['scheduled_at'],
                'status'       => $payload['status'],
                'dock_id'      => $payload['dock_id'] ?? $order->dock_id,
                'created_by'   => $authUser->id,
                'operator_id'  => $payload['operator_id'] ?? $order->operator_id,
            ]);

            OrderStatusHistory::query()->create([
                'loading_order_id' => $order->id,
                'old_status'       => $oldStatus,
                'new_status'       => $payload['status'],
                'changed_by'       => $authUser->id,
            ]);

            return $order;
        });
    }

}
