<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GetOrderTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_it_returns_order_with_valid_orderId(): void
    {
        $user = User::factory()->create([
            'email' => 'test@email.com',
            'password' => Hash::make('password'),
            'rule' => 'gestor',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $payload = [
            'source_system' => 'SAP',
            'loadingOrder' => [
                'external_id' => '123456789',
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

        // Cria a ordem via integração
        $this->postJson('/api/integration/order', $payload)->assertStatus(201);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/order/123456789');

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
            $payload = [
                'source_system' => 'SAP',
                'loadingOrder' => [
                    'external_id' => fake()->unique()->numerify('#########'),
                    'issue_date' => now()->format('Y-m-d'),
                    'status' => 'pending',

                    'customer' => [
                        'external_id' => fake()->numerify('#########'),
                        'name' => fake()->company(),
                    ],

                    'destination' => [
                        'external_id' => fake()->numerify('#########'),
                        'name' => fake()->company(),
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
                            'quantity' => 5,
                            'packages' => [
                                [
                                    'unique_package_code' => fake()->numerify('#########'),
                                    'quantity_in_package' => 5,
                                ]
                            ],
                        ],
                    ],
                ]
            ];

            $this->postJson('/api/integration/order', $payload)->assertStatus(201);
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
}
