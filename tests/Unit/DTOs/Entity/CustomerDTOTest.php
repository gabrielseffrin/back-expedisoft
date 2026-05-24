<?php

namespace Tests\Unit\DTOs\Entity;

use App\DTOs\Entity\CustomerDTO;
use Tests\TestCase;

class CustomerDTOTest extends TestCase
{
    public function test_it_creates_dto_from_array_with_all_fields(): void
    {
        $data = [
            'external_id'   => 'CUST-001',
            'name'          => 'Cliente Exemplo Ltda',
            'source_system' => 'ERP_A',
            'document'      => '12345678000100',
            'email'         => 'contato@cliente.com',
            'phone'         => '11999999999',
            'address'       => 'Rua das Flores, 123',
        ];

        $dto = CustomerDTO::fromArray($data);

        $this->assertSame('CUST-001', $dto->externalId);
        $this->assertSame('Cliente Exemplo Ltda', $dto->name);
        $this->assertSame('ERP_A', $dto->sourceSystem);
        $this->assertSame('12345678000100', $dto->document);
        $this->assertSame('contato@cliente.com', $dto->email);
        $this->assertSame('11999999999', $dto->phone);
        $this->assertSame('Rua das Flores, 123', $dto->address);
    }

    public function test_it_accepts_source_system_from_parameter(): void
    {
        $data = [
            'external_id' => 'CUST-002',
            'name'        => 'Cliente B',
        ];

        $dto = CustomerDTO::fromArray($data, 'ERP_EXTERNO');

        $this->assertSame('ERP_EXTERNO', $dto->sourceSystem);
    }

    public function test_array_source_system_takes_precedence(): void
    {
        $data = [
            'external_id'   => 'CUST-003',
            'name'          => 'Cliente C',
            'source_system' => 'SOURCE_FROM_ARRAY',
        ];

        $dto = CustomerDTO::fromArray($data, 'SOURCE_FROM_PARAM');

        $this->assertSame('SOURCE_FROM_ARRAY', $dto->sourceSystem);
    }

    public function test_optional_fields_default_to_null(): void
    {
        $dto = CustomerDTO::fromArray([
            'external_id' => 'CUST-004',
            'name'        => 'Cliente D',
        ]);

        $this->assertNull($dto->document);
        $this->assertNull($dto->email);
        $this->assertNull($dto->phone);
        $this->assertNull($dto->address);
        $this->assertNull($dto->sourceSystem);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new CustomerDTO(
            externalId: 'CUST-005',
            name: 'Cliente E',
            sourceSystem: 'ERP',
            email: 'e@mail.com',
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('external_id', $array);
        $this->assertArrayHasKey('source_system', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertSame('CUST-005', $array['external_id']);
    }

    public function test_dto_is_readonly(): void
    {
        $dto = new CustomerDTO(externalId: 'ID', name: 'Name');

        $this->expectException(\Error::class);
        $dto->name = 'Changed'; // @phpstan-ignore-line
    }
}
