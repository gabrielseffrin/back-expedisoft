<?php

namespace Tests\Feature;

use App\Jobs\ProcessLoadingOrderJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function apiHeaders(): array
    {
        return ['X-API-KEY' => config('services.integration.api_key')];
    }

    private function validPayload(): array
    {
        return [
            'source_system' => 'SAP',
            'loadingOrder' => [
                'external_id' => 'PED-2025-001',
                'issue_date'  => '2025-10-20',
                'status'      => 'pending',
                'customer' => [
                    'external_id' => '123456789',
                    'name'        => 'Wood Company',
                    'document'    => '123456789',
                    'email'       => 'teste@email.com',
                    'phone'       => '999999999',
                ],
                'destination' => [
                    'external_id' => '987654321',
                    'name'        => 'Paranaguá Port',
                    'address'     => 'Paraná',
                    'postal_code' => '80000000',
                    'city'        => 'Paranaguá',
                    'state'       => 'PR',
                ],
                'carrier' => [
                    'external_id' => '555555555',
                    'name'        => 'Fast Logistics',
                    'document'    => '555555555',
                ],
                'vehicle' => [
                    'external_id'  => 'AAA1234',
                    'vehiclePlate' => 'AAA1234',
                    'model'        => 'Truck Model X',
                ],
                'driver' => [
                    'external_id' => '987654321',
                    'name'        => 'John Silva',
                    'document'    => '987654321',
                    'phone'       => '999999999',
                ],
                'items' => [
                    [
                        'product_sku'         => 'PRD-001',
                        'product_description' => 'Produto Exemplo',
                        'quantity'            => 10,
                        'unit'                => 'pcs',
                        'packages' => [
                            [
                                'unique_package_code'  => '123456789',
                                'quantity_in_package'  => 10,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_it_fails_without_api_key(): void
    {
        $response = $this->postJson('/api/integration/order', []);

        $response->assertStatus(401);
    }

    public function test_it_fails_when_payload_is_invalid(): void
    {
        $response = $this->postJson('/api/integration/order', [], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['loadingOrder']);
    }

    public function test_it_fails_when_payload_is_incomplete(): void
    {
        $payload = [
            'loadingOrder' => [
                'external_id' => '123456789',
            ],
        ];

        $response = $this->postJson('/api/integration/order', $payload, $this->apiHeaders());

        $response->assertStatus(422);
    }

    public function test_it_fails_when_items_array_is_empty(): void
    {
        $payload = $this->validPayload();
        $payload['loadingOrder']['items'] = [];

        $response = $this->postJson('/api/integration/order', $payload, $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['loadingOrder.items']);
    }

    public function test_it_dispatches_job_with_valid_payload(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/integration/order', $this->validPayload(), $this->apiHeaders());

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'message' => 'Payload received and queued for processing',
            ]);

        Queue::assertPushed(ProcessLoadingOrderJob::class);
    }

    public function test_it_fails_with_invalid_api_key(): void
    {
        $response = $this->postJson(
            '/api/integration/order',
            [],
            ['X-API-KEY' => 'token-invalido-qualquer']
        );

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized. Invalid or missing API token.']);
    }
}
