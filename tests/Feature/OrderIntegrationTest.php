<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderIntegrationTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_it_fails_when_payload_is_invalid(): void
    {
        $payload = [];

        $response = $this->postJson('/api/integration/order', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'loadingOrder'
            ]);
    }

    public function test_it_fails_when_payload_is_incompleted(): void
    {
        $payload = [
            'loadingOrder' => [
                'externalId' => '123456789',
            ]
        ];

        $response = $this->postJson('/api/integration/order', $payload);
        $response->assertStatus(422);
    }

    public function test_it_creates_loading_order_with_valid_payload(): void
    {
        $payload = [
            'source_system' => 'SAP',
            'loadingOrder' => [
                'external_id' => 'PED-2025-001',
                'issue_date' => '2025-10-20',
                'status' => 'pending',
                'customer' => [
                    'external_id' => '123456789',
                    'name' => 'Wood Company',
                    'document' => '123456789',
                    'email' => 'teste@email.com',
                    'phone' => '999999999',
                ],
                'destination' => [
                    'external_id' => '987654321',
                    'name' => 'Paranaguá Port',
                    'address' => 'Paraná',
                    'postal_code' => '80000000',
                    'city' => 'Paranaguá',
                    'state' => 'PR',
                ],
                'carrier' => [
                    'external_id' => '555555555',
                    'name' => 'Fast Logistics',
                    'document' => '555555555',
                ],
                'vehicle' => [
                    'external_id' => 'AAA1234',
                    'vehiclePlate' => 'AAA1234',
                    'model' => 'Truck Model X',
                ],
                'driver' => [
                    'external_id' => '987654321',
                    'name' => 'John Silva',
                    'document' => '987654321',
                    'phone' => '999999999',
                ],
                'items' => [
                    [
                        'external_id' => 'ITEM-001',
                        'product_sku' => 'PRD-001',
                        'product_description' => 'MDF Panel',
                        'quantity' => 10,
                        'uniquePackageCode' => '1001',
                        'weight' => 500,
                        'volume' => 2.5,
                    ]
                ],
            ]
        ];

        $response = $this->postJson('/api/integration/order', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Payload received successfully'
            ]);
    }

    public function test_it_fails_when_items_array_is_empty(): void
    {
        $payload = [
            'loadingOrder' => [
                'externalId' => 'PED-001',
                'issueDate' => '2025-10-20',
                'status' => 'waiting',
                'customer' => [
                    'name' => 'Cliente',
                    'taxId' => '123',
                ],
                'destination' => [
                    'location' => 'Porto',
                ],
                'carrier' => [
                    'name' => 'Transportadora',
                    'vehiclePlate' => 'AAA1234',
                    'driver' => [
                        'name' => 'Motorista',
                    ],
                ],
                'items' => []
            ]
        ];

        $response = $this->postJson('/api/integration/order', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['loadingOrder.items']);
    }


}
