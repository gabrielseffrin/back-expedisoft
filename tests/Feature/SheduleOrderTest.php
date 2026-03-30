<?php

namespace Tests\Feature;

use App\Models\LoadingOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Support\Str;

class SheduleOrderTest extends TestCase
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

    public function test_schedule_order_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'test@email.com',
            'password' => Hash::make('password'),
            'rule' => 'operador',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $this->postJson('/api/integration/order', $this->validPayload(), $this->apiHeaders());

        $order = LoadingOrder::query()->where('external_id', 'PED-2025-001')->firstOrFail();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/order/schedule-order', [
                'id' => $order->id,
                'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
                'operator_id' => $user->id,
            ]);

        $response->assertStatus(200);
    }

    public function test_failed_when_order_not_found(): void
    {
        $user = User::factory()->create(['rule' => 'operador']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/order/schedule-order', [
                'id' => (string)Str::uuid(),
                'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'status' => 'scheduled',
                'operator_id' => $user->id,
            ]);

        $response->assertStatus(404);
    }

    public function test_failed_when_payload_is_invalid(): void
    {
        $user = User::factory()->create(['rule' => 'operador']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/order/schedule-order', [
                'id' => (string)Str::uuid(),
            ]);

        $response->assertStatus(422);
    }
}
