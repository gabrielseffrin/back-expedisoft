<?php

namespace Tests\Feature;

use App\Models\LoadingOrder;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPhotoListTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_it_lists_order_photos_for_operator(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-PHOTO-LIST-1', 'in_progress');

        $firstPhoto = Photo::query()->create([
            'loading_order_id' => $order->id,
            'storage_path' => 'Cargas/' . $order->external_id . '/first.png',
            'drive_id' => 'drive-first',
            'mime' => 'image/png',
            'status' => Photo::STATUS_UPLOADED,
            'uploaded_by' => $user->id,
            'uploaded_at' => now()->subMinute(),
        ]);

        $secondPhoto = Photo::query()->create([
            'loading_order_id' => $order->id,
            'storage_path' => 'Cargas/' . $order->external_id . '/second.jpg',
            'drive_id' => 'drive-second',
            'mime' => 'image/jpeg',
            'status' => Photo::STATUS_PENDING,
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/order/{$order->id}/photos");

        $response->assertStatus(200)
            ->assertJsonPath('data.count', 2)
            ->assertJsonCount(2, 'data.photos')
            ->assertJsonFragment(['id' => $firstPhoto->id])
            ->assertJsonFragment(['id' => $secondPhoto->id])
            ->assertJsonFragment(['url' => 'https://drive.google.com/uc?id=' . $firstPhoto->drive_id])
            ->assertJsonFragment(['url' => 'https://drive.google.com/uc?id=' . $secondPhoto->drive_id]);
    }

    public function test_it_returns_empty_list_when_no_photos_exist(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-PHOTO-LIST-2', 'in_progress');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/order/{$order->id}/photos");

        $response->assertStatus(200)
            ->assertJsonPath('data.count', 0)
            ->assertJsonCount(0, 'data.photos');
    }

    public function test_it_fails_to_list_photos_for_another_operator(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-PHOTO-LIST-3', 'in_progress');

        $otherUser = User::factory()->create(['rule' => 'operador']);
        $order->update(['operator_id' => $otherUser->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/order/{$order->id}/photos");

        $response->assertStatus(403);
    }

    public function test_it_fails_to_list_photos_for_missing_order(): void
    {
        $user = User::factory()->create(['rule' => 'operador']);
        $token = $user->createToken('test-token')->plainTextToken;
        $missingOrderId = '00000000-0000-0000-0000-000000000000';

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/order/{$missingOrderId}/photos");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Ordem de carregamento não encontrada.']);
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
