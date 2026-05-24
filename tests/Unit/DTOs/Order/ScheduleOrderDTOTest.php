<?php

namespace Tests\Unit\DTOs\Order;

use App\DTOs\Order\ScheduleOrderDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class ScheduleOrderDTOTest extends TestCase
{
    public function test_it_creates_dto_with_all_fields(): void
    {
        $dto = new ScheduleOrderDTO(
            id: 'order-uuid-123',
            scheduledAt: '2025-01-15 10:00:00',
            status: 'scheduled',
            dockId: 'dock-uuid-456',
            operatorId: 'operator-uuid-789',
        );

        $this->assertSame('order-uuid-123', $dto->id);
        $this->assertSame('2025-01-15 10:00:00', $dto->scheduledAt);
        $this->assertSame('scheduled', $dto->status);
        $this->assertSame('dock-uuid-456', $dto->dockId);
        $this->assertSame('operator-uuid-789', $dto->operatorId);
    }

    public function test_it_creates_dto_with_optional_fields_null(): void
    {
        $dto = new ScheduleOrderDTO(
            id: 'order-uuid-123',
            scheduledAt: '2025-01-15 10:00:00',
            status: 'scheduled',
        );

        $this->assertNull($dto->dockId);
        $this->assertNull($dto->operatorId);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new ScheduleOrderDTO(
            id: 'order-uuid-123',
            scheduledAt: '2025-01-15 10:00:00',
            status: 'scheduled',
            dockId: 'dock-uuid',
            operatorId: 'op-uuid',
        );

        $array = $dto->toArray();

        $this->assertSame([
            'id'           => 'order-uuid-123',
            'scheduled_at' => '2025-01-15 10:00:00',
            'status'       => 'scheduled',
            'dock_id'      => 'dock-uuid',
            'operator_id'  => 'op-uuid',
        ], $array);
    }

    public function test_dto_is_readonly(): void
    {
        $dto = new ScheduleOrderDTO(
            id: 'order-uuid-123',
            scheduledAt: '2025-01-15 10:00:00',
            status: 'scheduled',
        );

        $this->expectException(\Error::class);
        $dto->id = 'new-id'; // @phpstan-ignore-line
    }
}
