<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GetCheckListEntryTest extends TestCase
{

    use refreshDatabase;

    public function test_it_fails_when_orderId_doesnt_exists(): void
    {
        $user = User::factory()->create([
            'email' => 'test@email.com',
            'password' => Hash::make('password'),
            'rule' => 'gestor',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/order/123456789');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Order not found',
            ]);
    }

    public function test_it_fails_when_user_is_not_authenticated(): void
    {
        $response = $this->getJson('/api/order/123456789');

        $response->assertStatus(401);
    }

    public function test_it_returns_checklist_entry_with_valid_orderId(): void
    {
        $user = User::factory()->create([
            'email' => 'test@email.com',
            'password' => Hash::make('password'),
            'rule' => 'gestor',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/order/123456789');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'loading_order_id',
                'package_id',
                'scanned_by',
                'scanned_at',
                'scanned_code',
                'result'
            ]);

    }

    private function getIntegrationHeaders(): array
    {
        return ['X-API-KEY' => config('services.integration.api_key')];
    }

    private function buildOrderPayload(string $externalId): array
    {
        return [
            'source_system' => 'SAP',
            'loadingOrder' => [
                'external_id' => $externalId,
                'issue_date' => now()->format('Y-m-d'),
                'status' => 'pending',

                'customer' => [
                    'external_id' => fake()->numerify('#########'),
                    'name' => fake()->company(),
                ],

                'destination' => [
                    'external_id' => fake()->numerify('#########'),
                    'name' => fake()->company(),
                    'address' => fake()->address(),
                    'city' => fake()->city(),
                    'state' => fake()->stateAbbr(),
                    'postal_code' => fake()->numerify('########'),
                ],

                'carrier' => [
                    'external_id' => fake()->numerify('#########'),
                    'name' => fake()->company(),
                ],

                'vehicle' => [
                    'external_id' => fake()->numerify('#########'),
                    'vehiclePlate' => fake()->bothify('???-####'),
                ],

                'driver' => [
                    'external_id' => fake()->numerify('#########'),
                    'name' => fake()->name(),
                ],

                'items' => [
                    [
                        'product_sku' => fake()->bothify('PRD-###'),
                        'product_description' => fake()->sentence(),
                        'quantity' => 10,
                        'unit' => 'pcs',
                        'packages' => [
                            [
                                'unique_package_code' => fake()->numerify('#########'),
                                'quantity_in_package' => 10,
                            ]
                        ],
                    ],
                ],
            ]
        ];
    }
}
