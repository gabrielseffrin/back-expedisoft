<?php

namespace App\Services;

use App\DTOs\Order\FinishOrderDTO;
use App\DTOs\Order\ScheduleOrderDTO;
use App\Models\LoadingOrder;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OrderService
{
    public function findOrderById(string $orderId): LoadingOrder
    {
        return LoadingOrder::with('customer', 'destination', 'carrier', 'driver', 'vehicle', 'operator', 'dock', 'items.product', 'items.packages.checklistEntry.scannedBy')->findOrFail($orderId);
    }

    public function getAllOrders(string $perPage): LengthAwarePaginator
    {
        return LoadingOrder::with('customer', 'destination', 'carrier', 'driver', 'vehicle', 'operator', 'dock')->paginate($perPage);
    }

    public function getOrdersByCurrentUser(string $perPage, User $user): LengthAwarePaginator
    {
        return LoadingOrder::with('customer', 'destination', 'carrier', 'driver', 'vehicle', 'operator', 'dock')
            ->where('created_by', $user->id)
            ->orWhere('operator_id', $user->id)
            ->orderBy('scheduled_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @throws AuthorizationException
     */
    public function scheduleOrder(User $user, ScheduleOrderDTO $dto): LoadingOrder
    {
        $order = LoadingOrder::query()->findOrFail($dto->id);

        if (isset($dto->operatorId)) {
            $targetOperator = User::query()->findOrFail($dto->operatorId);

            if ($targetOperator->rule !== 'operador') {
                throw new AuthorizationException('O usuário selecionado não possui permissão de operador.');
            }
        }

        $updateData = [
            'scheduled_at' => $dto->scheduledAt,
            'dock_id'      => $dto->dockId ?? $order->dock_id,
            'created_by'   => $user->id,
            'operator_id'  => $dto->operatorId ?? $order->operator_id,
        ];

        return $this->updateOrderAndRecordHistory($order, $dto->status, $user, $updateData);
    }

    /**
     * @throws AuthorizationException
     */
    public function startOrder(User $operator, string $orderId): LoadingOrder
    {
        $order = LoadingOrder::query()->findOrFail($orderId);

        if ($order->operator_id !== $operator->id && $operator->rule !== 'admin') {
            throw new AuthorizationException('Você não tem permissão para iniciar esta ordem.');
        }

        if ($order->status !== 'scheduled') {
            throw new BadRequestException('A ordem de carregamento deve estar no status "scheduled" para ser iniciada.');
        }

        $updateData = [
            'started_at' => now(),
        ];

        return $this->updateOrderAndRecordHistory($order, 'in_progress', $operator, $updateData);
    }

    /**
     * @throws AuthorizationException
     */
    public function finishOrder(User $operator, string $orderId, FinishOrderDTO $dto): LoadingOrder
    {
        $order = LoadingOrder::with('items.packages.checklistEntry')->findOrFail($orderId);

        if ($order->operator_id !== $operator->id && $operator->rule !== 'admin') {
            throw new AuthorizationException('Você não tem permissão para finalizar esta ordem.');
        }

        if ($order->status !== 'in_progress') {
            throw new BadRequestException('A ordem de carregamento deve estar no status "in_progress" para ser finalizada.');
        }

        $totalPackages   = $order->items->flatMap->packages->count();
        $checkedPackages = $order->items->flatMap->packages->filter(fn ($package) => $package->checklistEntry !== null)->count();
        $isDivergent     = $checkedPackages < $totalPackages;

        if ($isDivergent && empty($dto->justification)) {
            throw new BadRequestException('A justificativa é obrigatória para finalizar uma carga incompleta.');
        }

        $updateData = [
            'completed_at' => now(),
        ];

        $newStatus = $isDivergent ? 'divergence' : 'completed';

        if (!empty($dto->justification)) {
            $updateData['justification'] = $dto->justification;
        }

        return $this->updateOrderAndRecordHistory($order, $newStatus, $operator, $updateData);
    }

    private function updateOrderAndRecordHistory(LoadingOrder $order, string $newStatus, User $user, array $updateData = []): LoadingOrder
    {
        return DB::transaction(function () use ($order, $newStatus, $user, $updateData) {
            $oldStatus = $order->status;

            $updateData['status'] = $newStatus;

            $order->update($updateData);

            if ($oldStatus !== $newStatus) {
                OrderStatusHistory::query()->create([
                    'loading_order_id' => $order->id,
                    'old_status'       => $oldStatus,
                    'new_status'       => $newStatus,
                    'changed_by'       => $user->id,
                ]);
            }

            return $order;
        });
    }
}
