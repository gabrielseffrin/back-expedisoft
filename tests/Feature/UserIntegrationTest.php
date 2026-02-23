<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserIntegrationTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_it_fails_when_payload_is_invalid(): void
    {
        $payload = [];

        $response = $this->postJson('/api/integration/user', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'user'
            ]);
    }

    public function test_it_fails_when_payload_is_imcompleted(): void
    {
        $payload = [
            'user' => [
                'external_id' => '123456789',
            ]
        ];

        $response = $this->postJson('/api/integration/user', $payload);
        $response->assertStatus(422);
    }

    public function test_it_creates_user_with_valid_payload(): void
    {
        $payload = [
            'source_system' => 'SAP',
            'user' => [
                'external_id' => '123456789',
                'name' => 'John Doe',
                'email' => 'teste@email.com',
            ]
        ];

        $response = $this->postJson('/api/integration/user', $payload);
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created or updated successfully',
                //'data' => null,
            ]);
    }
}
