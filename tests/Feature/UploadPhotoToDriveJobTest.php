<?php

namespace Tests\Feature;

use App\Jobs\UploadPhotoToDriveJob;
use App\Models\LoadingOrder;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Str;

class UploadPhotoToDriveJobTest extends TestCase
{
    use RefreshDatabase;

    private function setupOrderEnvironment(string $externalId, string $status = 'scheduled', $operator = null): array
    {
        $user = $operator ?? User::factory()->create(['rule' => 'operador']);

        $this->postJson('/api/integration/order', $this->buildOrderPayload($externalId), [
            'X-API-KEY' => config('services.integration.api_key')
        ]);

        $order = LoadingOrder::query()->where('external_id', $externalId)->first();
        $order->update([
            'status' => $status,
            'operator_id' => $user->id
        ]);

        return [$user, $order->fresh()];
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

    public function test_it_moves_the_photo_from_local_storage_to_google_drive_and_updates_db(): void
    {
        Storage::fake('local');
        Storage::fake('google');

        [$user, $order] = $this->setupOrderEnvironment('TESTE-DRIVE-123', 'in_progress');

        $photo = Photo::query()->create([
            'id' => Str::uuid(),
            'loading_order_id' => $order->id,
            'uploaded_by' => $user->id,
            'storage_path' => 'Processando...',
            'mime' => 'image/jpeg',
            'status' => Photo::STATUS_PENDING,
        ]);

        $localPath = 'temp_photos/falsaimagem.jpg';
        Storage::disk('local')->put($localPath, 'conteudo_da_imagem_falsa');

        $folderName = 'Cargas/' . $order->external_id;
        $job = new UploadPhotoToDriveJob($photo->id, $localPath, $folderName);
        $job->handle();

        Storage::disk('local')->assertMissing($localPath);

        $expectedDrivePath = $folderName . '/falsaimagem.jpg';
        Storage::disk('google')->assertExists($expectedDrivePath);

        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'storage_path' => $expectedDrivePath,
            'status' => Photo::STATUS_UPLOADED,
        ]);
    }
}
