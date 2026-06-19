<?php

namespace Tests\Unit\DTOs\Integration;

use App\DTOs\Integration\UserIntegrationDTO;
use Tests\TestCase;

class UserIntegrationDTOTest extends TestCase
{
    private array $validPayload = [
        'source_system' => 'SAP',
        'user'          => [
            'external_id' => 'USER-99',
            'name'        => 'João Silva',
            'email'       => 'joao@empresa.com',
        ],
    ];

    public function test_it_creates_dto_from_array(): void
    {
        $dto = UserIntegrationDTO::fromArray($this->validPayload);

        $this->assertSame('SAP', $dto->sourceSystem);
        $this->assertSame('USER-99', $dto->user->externalId);
        $this->assertSame('João Silva', $dto->user->name);
        $this->assertSame('joao@empresa.com', $dto->user->email);
        $this->assertSame('SAP', $dto->user->sourceSystem);
    }

    public function test_it_handles_missing_external_id(): void
    {
        $payload = [
            'source_system' => 'SAP',
            'user'          => [
                'name'  => 'Maria',
                'email' => 'maria@empresa.com',
            ],
        ];

        $dto = UserIntegrationDTO::fromArray($payload);

        $this->assertNull($dto->user->externalId);
    }

    public function test_dto_is_readonly(): void
    {
        $dto = UserIntegrationDTO::fromArray($this->validPayload);

        $this->expectException(\Error::class);
        $dto->sourceSystem = 'OTHER'; // @phpstan-ignore-line
    }
}
