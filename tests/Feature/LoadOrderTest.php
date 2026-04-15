<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LoadingOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoadOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to set up the testing environment.
     */
    private function setupOrderEnvironment(string $externalId, string $status = 'scheduled', $operator = null): array
    {
        $user = $operator ?? $this->createUser();
        $token = $user->createToken('test-token')->plainTextToken;

        // Integrates the order via the integration API
        $this->postJson('/api/integration/order', $this->buildOrderPayload($externalId), $this->getIntegrationHeaders());

        $order = LoadingOrder::where('external_id', $externalId)->first();
        $order->update([
            'status' => $status,
            'operator_id' => $user->id
        ]);

        return [$user, $token, $order->fresh()];
    }

    public function test_it_successfully_starts_a_scheduled_order_allocated_to_the_current_user(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-START-1', 'scheduled');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/start-order");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ordem iniciada com sucesso',
            ]);

        $this->assertEquals('in_progress', $order->fresh()->status);
    }

    public function test_it_successfully_scans_a_package_via_qr_code(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-QR-1', 'in_progress');

        $package = $order->items()->first()->packages()->first();
        $qrCodeScanned = $package->unique_package_code;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/checklist", [
                'qr_code' => $qrCodeScanned,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pacote conferido com sucesso'
            ]);
    }

    public function test_it_successfully_finishes_an_in_progress_order(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-FINISH-1', 'in_progress');

        // Opcional: Você pode precisar simular que todos os pacotes foram escaneados
        // dependendo da sua regra de negócio para permitir finalizar a ordem.

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/finish-order");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ordem finalizada com sucesso',
            ]);

        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_it_fails_to_start_an_order_that_is_not_scheduled(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-NOT-SCHED', 'pending');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/start-order");

        $response->assertStatus(400);
    }

    public function test_it_fails_to_finish_an_order_that_is_not_in_progress(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-NOT-IN-PROG', 'scheduled');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/finish-order");

        $response->assertStatus(400);
    }

    public function test_it_fails_to_scan_a_package_for_an_order_not_in_progress(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-NOT-STARTED', 'scheduled');
        $package = $order->items()->first()->packages()->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/checklist", [
                'qr_code' => $package->unique_package_code,
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'A ordem precisa estar em andamento para carregar itens.']);
    }

    public function test_it_fails_to_scan_a_package_allocated_to_another_order(): void
    {
        [$user, $token, $orderA] = $this->setupOrderEnvironment('ORD-A', 'in_progress');
        [$otherUser, $otherToken, $orderB] = $this->setupOrderEnvironment('ORD-B', 'in_progress');

        // Tries to scan Order B's package while assigned to Order A
        $packageB = $orderB->items()->first()->packages()->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$orderA->id}/checklist", [
                'qr_code' => $packageB->unique_package_code,
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Código QR não corresponde a nenhum pacote da ordem de carregamento.']);
    }

    public function test_it_fails_to_scan_an_invalid_package_code(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-INVALID', 'in_progress');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/checklist", [
                'qr_code' => 'NON_EXISTENT_CODE',
            ]);

        $response->assertStatus(400);
    }

    public function test_it_fails_to_scan_the_same_package_twice(): void
    {
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-DOUBLE', 'in_progress');
        $package = $order->items()->first()->packages()->first();

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/checklist", [
                'qr_code' => $package->unique_package_code,
            ])->assertStatus(200);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/checklist", [
                'qr_code' => $package->unique_package_code,
            ]);

        $response->assertStatus(400)
        ->assertJsonFragment(['message' => 'Este pacote já foi conferido.']);
    }

    public function test_it_fails_to_access_an_order_allocated_to_another_user(): void
    {
        $otherUser = User::factory()->create(['rule' => 'operador']);
        [$user, $token, $order] = $this->setupOrderEnvironment('ORD-WRONG-OP', 'scheduled');

        // Re-assigns the order to another person
        $order->update(['operator_id' => $otherUser->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson("/api/order/{$order->id}/start-order");

        $response->assertStatus(403);
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

    private function createUser(): User
    {
        return User::factory()->create(['rule' => 'operador']);
    }
}
