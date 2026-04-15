<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GetOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.integration.api_key' => '123456']);
    }

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
                'message' => 'Ordem de carregamento não encontrada.',
            ]);
    }

    public function test_it_returns_order_with_valid_orderId(): void
    {
        $user = User::factory()->create([
            'email' => 'test@email.com',
            'password' => Hash::make('password'),
            'rule' => 'gestor',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $payload = $this->buildOrderPayload('123456789');
        $creationResponse = $this->postJson('/api/integration/order', $payload, $this->getIntegrationHeaders());
        $creationResponse->assertStatus(202);

        $order = \App\Models\LoadingOrder::query()->where('external_id', '123456789')->firstOrFail();
        $orderId = $order->id;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/order/' . $orderId);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'external_id',
                    'issue_date',
                    'status',
                    'customer',
                    'destination',
                    'carrier',
                    'vehicle',
                    'driver',
                    'items' => [
                        '*' => [
                            'product',
                            'quantity',
                            'packages' => [
                                '*' => [
                                    'unique_package_code',
                                    'quantity_in_package',
                                ]
                            ]
                        ]
                    ],
                ]
            ])
            ->assertJsonPath('data.external_id', '123456789');
    }

    public function test_it_returns_all_orders_when_no_orderId_is_provided(): void
    {
        $user = User::factory()->create([
            'email' => 'test@email.com',
            'password' => Hash::make('password'),
            'rule' => 'gestor',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $ordersCount = 3;

        for ($i = 0; $i < $ordersCount; $i++) {
            $payload = $this->buildOrderPayload(fake()->unique()->numerify('#########'));
            $this->postJson('/api/integration/order', $payload, $this->getIntegrationHeaders())->assertStatus(202);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/order');

        $response->assertStatus(200)
            ->assertJsonCount($ordersCount, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'external_id',
                        'issue_date',
                        'status',
                        'customer',
                        'destination',
                        'carrier',
                        'vehicle',
                        'driver',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    public function test_it_returns_only_orders_belonging_to_the_logged_in_user(): void
    {
        $loggedInUser = User::factory()->create([
            'email' => 'operador_logado@email.com',
            'password' => Hash::make('password'),
            'rule' => 'operador',
        ]);
        $token = $loggedInUser->createToken('test-token')->plainTextToken;

        $otherUser = User::factory()->create([
            'email' => 'outro_operador@email.com',
            'password' => Hash::make('password'),
            'rule' => 'operador',
        ]);

        $aux = $this->postJson('/api/integration/order', $this->buildOrderPayload('MY-ORD-1'), $this->getIntegrationHeaders());
        $this->postJson('/api/integration/order', $this->buildOrderPayload('MY-ORD-2'), $this->getIntegrationHeaders());
        $this->postJson('/api/integration/order', $this->buildOrderPayload('OTHER-ORD-3'), $this->getIntegrationHeaders());

        \App\Models\LoadingOrder::query()->where('external_id', 'MY-ORD-1')->update(['operator_id' => $loggedInUser->id]);
        \App\Models\LoadingOrder::query()->where('external_id', 'MY-ORD-2')->update(['operator_id' => $loggedInUser->id]);
        \App\Models\LoadingOrder::query()->where('external_id', 'OTHER-ORD-3')->update(['operator_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/order/my-orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['external_id' => 'MY-ORD-1'])
            ->assertJsonFragment(['external_id' => 'MY-ORD-2'])
            ->assertJsonMissing(['external_id' => 'OTHER-ORD-3']);
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
