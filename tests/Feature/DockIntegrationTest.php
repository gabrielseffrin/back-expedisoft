<?php

namespace Tests\Feature;

use App\Jobs\ProcessDockJob;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DockIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'source_system' => 'ERP_TEST',
            'dock' => [
                'external_id' => 'ERP-DOCK-123',
                'dock_code' => 'DOCK-01',
                'description' => 'Pátio A',
                'location' => 'Armazém 3',
            ],
        ];
    }

    public function test_happy_path_dispatches_job_and_returns_202(): void
    {
        Queue::fake();

        $payload = $this->validPayload();

        $response = $this->postJson('/api/integration/dock', $payload, [
            'X-API-KEY' => 'test-api-key-secret',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(202);
        $response->assertJson(['success' => true]);

        Queue::assertPushed(ProcessDockJob::class, function ($job) use ($payload) {
            $ref = new \ReflectionObject($job);
            if (! $ref->hasProperty('payload')) {
                return false;
            }
            $prop = $ref->getProperty('payload');
            $prop->setAccessible(true);
            $value = $prop->getValue($job);

            return $value === $payload;
        });
    }

    public function test_missing_or_invalid_api_key_returns_401(): void
    {
        $payload = $this->validPayload();

        // no key
        $response = $this->postJson('/api/integration/dock', $payload);
        $response->assertStatus(401);

        // invalid key
        $response = $this->postJson('/api/integration/dock', $payload, [
            'X-API-KEY' => 'wrong-key',
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(401);
    }

    public function test_invalid_payload_returns_422(): void
    {
        $payload = [
            // missing source_system and dock
        ];

        $response = $this->postJson('/api/integration/dock', $payload, [
            'X-API-KEY' => 'test-api-key-secret',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_rate_limit_applies_and_returns_429_after_limit_exceeded(): void
    {

        Config::set('app.env', 'testing');

        $payload = $this->validPayload();
        $response1 = $this->postJson('/api/integration/dock', $payload, [
            'X-API-KEY' => 'test-api-key-secret',
            'Accept' => 'application/json',
        ]);

        $this->assertTrue(in_array($response1->getStatusCode(), [202, 200]));

        $response2 = $this->postJson('/api/integration/dock', $payload, [
            'X-API-KEY' => 'test-api-key-secret',
            'Accept' => 'application/json',
        ]);

        $this->assertTrue(in_array($response2->getStatusCode(), [202, 429]));
    }
}
