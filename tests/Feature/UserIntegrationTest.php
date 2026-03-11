<?php

namespace Tests\Feature;

use App\Jobs\ProcessUserJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UserIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function apiHeaders(): array
    {
        return ['X-API-KEY' => config('services.integration.api_key')];
    }

    public function test_it_fails_without_api_key(): void
    {
        $response = $this->postJson('/api/integration/user', []);

        $response->assertStatus(401);
    }

    public function test_it_fails_when_payload_is_invalid(): void
    {
        $response = $this->postJson('/api/integration/user', [], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user']);
    }

    public function test_it_fails_when_payload_is_incomplete(): void
    {
        $payload = [
            'user' => [
                'external_id' => '123456789',
            ],
        ];

        $response = $this->postJson('/api/integration/user', $payload, $this->apiHeaders());

        $response->assertStatus(422);
    }

    public function test_it_dispatches_job_with_valid_payload(): void
    {
        Queue::fake();

        $payload = [
            'source_system' => 'SAP',
            'user' => [
                'external_id' => '123456789',
                'name'        => 'John Doe',
                'email'       => 'teste@email.com',
            ],
        ];

        $response = $this->postJson('/api/integration/user', $payload, $this->apiHeaders());

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'message' => 'Payload received and queued for processing',
            ]);

        Queue::assertPushed(ProcessUserJob::class);
    }

    public function test_it_fails_with_invalid_api_key(): void
    {
        $response = $this->postJson(
            '/api/integration/user',
            [],
            ['X-API-KEY' => 'token-invalido-qualquer']
        );

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized. Invalid or missing API token.']);
    }
}
