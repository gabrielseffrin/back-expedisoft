<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class LoadingOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numerify('#########'),
            'issued_at' => fake()->dateTimeThisYear(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
            'customer' => [
                'external_id' => fake()->unique()->numerify('#########'),
                'name' => fake()->company(),
                'document' => fake()->numerify('#########'),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->numerify('#########'),
            ],
            'destination' => [
                'external_id' => fake()->unique()->numerify('#########'),
                'name' => fake()->company(),
                'address' => fake()->address(),
                'postal_code' => fake()->numerify('#########'),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
            ],
            'carrier' => [
                'external_id' => fake()->unique()->numerify('#########'),
                'name' => fake()->company(),
                'document' => fake()->numerify('#########'),
            ],
            'vehicle' => [
                'external_id' => fake()->unique()->numerify('#########'),
                'vehiclePlate' => fake()->bothify('???-####'),
                'model' => fake()->word(),
            ],
            'driver' => [
                'external_id' => fake()->unique()->numerify('#########'),
                'name' => fake()->name(),
                'document' => fake()->numerify('#########'),
                'phone' => fake()->numerify('#########'),
            ],
            'items' => [
                [
                    'external_id' => fake()->unique()->numerify('#########'),
                    'product_sku' => fake()->bothify('PRD-###'),
                    'product_description' => fake()->sentence(),
                    'quantity' => fake()->numberBetween(1, 100),
                    'uniquePackageCode' => fake()->unique()->numerify('#########'),
                    'weight' => fake()->randomFloat(2, 0.1, 1000),
                    'volume' => fake()->randomFloat(2, 0.1, 1000),
                ],
            ],
        ];
    }
}
