<?php

namespace App\DTOs\Order;

use App\Http\Requests\ScheduleOrderRequest;

readonly class ScheduleOrderDTO
{
    public function __construct(
        public string  $id,
        public string  $scheduledAt,
        public string  $status,
        public ?string $dockId = null,
        public ?string $operatorId = null,
    ) {
    }

    public static function fromRequest(ScheduleOrderRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            id: $validated['id'],
            scheduledAt: $validated['scheduled_at'],
            status: $validated['status'],
            dockId: $validated['dock_id'] ?? null,
            operatorId: $validated['operator_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'scheduled_at' => $this->scheduledAt,
            'status'       => $this->status,
            'dock_id'      => $this->dockId,
            'operator_id'  => $this->operatorId,
        ];
    }
}
