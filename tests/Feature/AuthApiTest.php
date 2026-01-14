<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function testLogin(): void
    {

        $user = User::factory()->create([
            'email' => 'test@email',
            'password' => Hash::make('password'),
            'rule' => 'gestor',
        ]);

        $data = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    public function testLogin401(): void
    {

        $user = User::factory()->create([
            'email' => 'test@email',
            'password' => Hash::make('password'),
            'rule' => 'gestor',
        ]);

        $data = [
            'email' => $user->email,
            'password' => 'passwordd',
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid credentials'
        ]);
    }

    public function testLoginNoUser(): void
    {
        $data = [
            'email' => '',
            'password' => 'passwordd',
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Invalid credentials'
        ]);
    }

    public function testLogout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
