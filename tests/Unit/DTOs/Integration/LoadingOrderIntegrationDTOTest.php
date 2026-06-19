<?php

namespace Tests\Unit\DTOs\Integration;

use App\DTOs\Integration\LoadingOrderIntegrationDTO;
use Tests\TestCase;

class LoadingOrderIntegrationDTOTest extends TestCase
{
    private array $validPayload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validPayload = [
            'source_system' => 'ERP_A',
            'loadingOrder'  => [
                'external_id' => 'DOC-001',
                'issue_date'  => '2025-01-07',
                'status'      => 'pending',
                'notes'       => 'Observação de teste',
                'customer'    => [
                    'external_id' => 'CUST-01',
                    'name'        => 'Cliente Teste',
                    'email'       => 'cliente@teste.com',
                    'phone'       => '11999999999',
                ],
                'destination' => [
                    'external_id' => 'DEST-01',
                    'name'        => 'CD Sul',
                    'city'        => 'Porto Alegre',
                    'state'       => 'RS',
                    'postal_code' => '90000000',
                ],
                'carrier' => [
                    'external_id' => 'CARR-01',
                    'name'        => 'Transportadora X',
                    'document'    => '12345678000100',
                    'phone'       => '11988888888',
                ],
                'vehicle' => [
                    'external_id'  => 'VEH-01',
                    'vehiclePlate' => 'ABC1234',
                    'model'        => 'Truck 2022',
                ],
                'driver' => [
                    'external_id' => 'DRV-01',
                    'name'        => 'Motorista Teste',
                    'document'    => '12345678900',
                    'phone'       => '11977777777',
                ],
                'items' => [
                    [
                        'product_sku'         => 'SKU-001',
                        'product_description' => 'Produto Teste',
                        'quantity'            => 10,
                        'unit'                => 'un',
                        'packages'            => [
                            [
                                'unique_package_code'  => 'PKG-001',
                                'quantity_in_package'  => 5,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_it_creates_dto_from_array(): void
    {
        $dto = LoadingOrderIntegrationDTO::fromArray($this->validPayload);

        $this->assertSame('ERP_A', $dto->sourceSystem);
        $this->assertSame('DOC-001', $dto->externalId);
        $this->assertSame('2025-01-07', $dto->issueDate);
        $this->assertSame('pending', $dto->status);
        $this->assertSame('Observação de teste', $dto->notes);
    }

    public function test_it_maps_customer_correctly(): void
    {
        $dto = LoadingOrderIntegrationDTO::fromArray($this->validPayload);

        $this->assertSame('CUST-01', $dto->customer->externalId);
        $this->assertSame('Cliente Teste', $dto->customer->name);
        $this->assertSame('ERP_A', $dto->customer->sourceSystem);
    }

    public function test_it_maps_destination_correctly(): void
    {
        $dto = LoadingOrderIntegrationDTO::fromArray($this->validPayload);

        $this->assertSame('DEST-01', $dto->destination->externalId);
        $this->assertSame('CD Sul', $dto->destination->name);
        $this->assertSame('RS', $dto->destination->state);
    }

    public function test_it_maps_items_and_packages_correctly(): void
    {
        $dto = LoadingOrderIntegrationDTO::fromArray($this->validPayload);

        $this->assertCount(1, $dto->items);

        $item = $dto->items[0];
        $this->assertSame('SKU-001', $item->product->sku);
        $this->assertSame(10, $item->quantity);
        $this->assertCount(1, $item->packages);
        $this->assertSame('PKG-001', $item->packages[0]->uniquePackageCode);
        $this->assertSame(5, $item->packages[0]->quantityInPackage);
    }

    public function test_it_handles_items_with_no_packages(): void
    {
        $this->validPayload['loadingOrder']['items'][0]['packages'] = [];

        $dto = LoadingOrderIntegrationDTO::fromArray($this->validPayload);

        $this->assertCount(0, $dto->items[0]->packages);
    }

    public function test_status_defaults_to_pending_when_not_provided(): void
    {
        unset($this->validPayload['loadingOrder']['status']);
        $this->validPayload['loadingOrder']['status'] = null;

        // Recria com status null → deve usar default 'pending'
        $payload = $this->validPayload;
        $payload['loadingOrder']['status'] = null;

        $dto = LoadingOrderIntegrationDTO::fromArray($payload);

        $this->assertSame('pending', $dto->status);
    }
}
