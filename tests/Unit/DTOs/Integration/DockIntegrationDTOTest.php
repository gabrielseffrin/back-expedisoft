<?php

namespace Tests\Unit\DTOs\Integration;

use App\DTOs\Integration\DockIntegrationDTO;
use Tests\TestCase;

class DockIntegrationDTOTest extends TestCase
{
    private array $validPayload = [
        'source_system' => 'ERP_SISTEMA_A',
        'dock'          => [
            'external_id' => 'DOCK-001',
            'dock_code'   => 'D01',
            'description' => 'Doca principal',
            'location'    => 'Bloco A',
        ],
    ];

    public function test_it_creates_dto_from_array(): void
    {
        $dto = DockIntegrationDTO::fromArray($this->validPayload);

        $this->assertSame('ERP_SISTEMA_A', $dto->sourceSystem);
        $this->assertSame('DOCK-001', $dto->dock->externalId);
        $this->assertSame('D01', $dto->dock->dockCode);
        $this->assertSame('Doca principal', $dto->dock->description);
        $this->assertSame('Bloco A', $dto->dock->location);
        $this->assertSame('ERP_SISTEMA_A', $dto->dock->sourceSystem);
    }

    public function test_it_handles_optional_dock_fields(): void
    {
        $payload = [
            'source_system' => 'ERP_SISTEMA_A',
            'dock'          => [
                'external_id' => 'DOCK-002',
                'dock_code'   => 'D02',
            ],
        ];

        $dto = DockIntegrationDTO::fromArray($payload);

        $this->assertNull($dto->dock->description);
        $this->assertNull($dto->dock->location);
    }

    public function test_dto_is_readonly(): void
    {
        $dto = DockIntegrationDTO::fromArray($this->validPayload);

        $this->expectException(\Error::class);
        $dto->sourceSystem = 'OTHER'; // @phpstan-ignore-line
    }
}
