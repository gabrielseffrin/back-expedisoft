<?php

namespace Tests\Feature;

use App\Jobs\UploadPhotoToDriveJob;
use App\Models\LoadingOrder;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to set up the testing environment.
     */
    private function setupOrderEnvironment(string $externalId, string $status = 'scheduled', $operator = null): array
    {
        $user = $operator ?? User::factory()->create(['rule' => 'operador']);
        $token = $user->createToken('test-token')->plainTextToken;

        $this->postJson('/api/integration/order', $this->buildOrderPayload($externalId), [
            'X-API-KEY' => config('services.integration.api_key')
        ]);

        $order = LoadingOrder::query()->where('external_id', $externalId)->first();
        $order->update([
            'status' => $status,
            'operator_id' => $user->id
        ]);

        return [$user, $token, $order->fresh()];
    }

    public function test_it_accepts_a_photo_and_dispatches_a_job_to_the_queue(): void
    {
        Storage::fake('local');

        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-PHOTO-1', 'in_progress');

        Queue::fake([UploadPhotoToDriveJob::class]);

        $files = [
            UploadedFile::fake()->image('comprovativo-1.jpg'),
            UploadedFile::fake()->image('comprovativo-2.jpg'),
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/photos", [
                'photos' => $files,
            ]);

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'message' => 'Fotos recebidas e entraram na fila de processamento.',
                'data' => [
                    'count' => 2,
                ],
            ]);

        $this->assertDatabaseHas('photos', [
            'loading_order_id' => $order->id,
            'storage_path' => 'Processando...',
            'mime' => 'image/jpeg',
            'uploaded_by' => $user->id,
            'status' => Photo::STATUS_PENDING,
        ]);

        $this->assertCount(2, Queue::pushed(UploadPhotoToDriveJob::class));
        Queue::assertPushed(UploadPhotoToDriveJob::class, function ($job) use ($order) {
            return $job->folderName === 'Cargas/' . $order->external_id;
        });
    }

    public function test_it_fails_to_upload_a_photo_if_order_is_not_in_progress(): void
    {
        Storage::fake('local');

        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-PHOTO-2', 'scheduled');

        Queue::fake([UploadPhotoToDriveJob::class]);

        $file = UploadedFile::fake()->image('carga.jpg');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/photos", [
                'photos' => [$file],
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'A ordem precisa estar em andamento para anexar fotos.']);

        Queue::assertNothingPushed();
    }

    public function test_it_fails_to_upload_a_photo_allocated_to_another_user(): void
    {
        $otherUser = User::factory()->create(['rule' => 'operador']);

        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-PHOTO-3', 'in_progress');

        $order->update(['operator_id' => $otherUser->id]);

        Queue::fake([UploadPhotoToDriveJob::class]);

        $file = UploadedFile::fake()->image('intruder.jpg');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/photos", [
                'photos' => [$file],
            ]);

        $response->assertStatus(403);
        Queue::assertNothingPushed();
    }

    public function test_it_fails_if_the_uploaded_file_is_not_an_image(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-PHOTO-4', 'in_progress');

        Queue::fake([UploadPhotoToDriveJob::class]);

        $file = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/photos", [
                'photos' => [$file],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photos.0']);

        Queue::assertNothingPushed();
    }

    public function test_it_fails_if_the_uploaded_image_is_too_large(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-PHOTO-5', 'in_progress');

        Queue::fake([UploadPhotoToDriveJob::class]);

        $file = UploadedFile::fake()->image('gigante.jpg')->size(100240);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/photos", [
                'photos' => [$file],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photos.0']);

        Queue::assertNothingPushed();
    }

    public function test_it_fails_to_upload_a_photo_for_a_missing_order(): void
    {
        Storage::fake('local');
        Queue::fake([UploadPhotoToDriveJob::class]);

        $user = User::factory()->create(['rule' => 'operador']);
        $token = $user->createToken('test-token')->plainTextToken;
        $missingOrderId = '00000000-0000-0000-0000-000000000000';

        $file = UploadedFile::fake()->image('inexistente.jpg');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$missingOrderId}/photos", [
                'photos' => [$file],
            ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Ordem de carregamento não encontrada.']);

        Queue::assertNothingPushed();
        $this->assertDatabaseMissing('photos', ['uploaded_by' => $user->id]);
    }

    private function buildOrderPayload(string $externalId): array
    {
        return [
            'source_system' => 'SAP',
            'loadingOrder' => [
                'external_id' => $externalId,
                'issue_date' => now()->format('Y-m-d'),
                'status' => 'pending',
                'customer' => ['external_id' => 'C1', 'name' => 'Test'],
                'destination' => [
                    'external_id' => 'D1', 'name' => 'Test', 'address' => 'X',
                    'city' => 'X', 'state' => 'ST', 'postal_code' => '12345678'
                ],
                'carrier' => ['external_id' => 'T1', 'name' => 'Test'],
                'vehicle' => ['external_id' => 'V1', 'vehiclePlate' => 'ABC1234'],
                'driver' => ['external_id' => 'DR1', 'name' => 'Test'],
                'items' => [[
                    'product_sku' => 'SKU1', 'product_description' => 'Desc', 'quantity' => 1, 'unit' => 'UN',
                    'packages' => [['unique_package_code' => 'PKG-' . $externalId, 'quantity_in_package' => 1]]
                ]],
            ]
        ];
    }
}
