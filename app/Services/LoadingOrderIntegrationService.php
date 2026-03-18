<?php

namespace App\Services;


use App\Enums\HttpStatus;
use App\Exceptions\IntegrationException;
use App\Models\LoadingOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class LoadingOrderIntegrationService
{

    //protected EntityService $entityService;

    private const ENDPOINT = '/api/integration/order';

    public function __construct(private EntityService $entityService, private IntegrationLogService $logService)
    {
        //$this->entityService = $entityService;

    }

    /**
     * @param array $payload
     * @return LoadingOrder
     * @throws IntegrationException|\Exception
     */
    public function storeOrder(array $payload): LoadingOrder
    {
        DB::beginTransaction();
        try {

            $this->validateData($payload);

            $sourceSystem = $payload['source_system'];
            $orderData = $payload['loadingOrder'];

            //dd($orderData);
            $customer = $this->entityService->findOrCreateCustomer([
                'external_id' => $orderData['customer']['external_id'],
                'source_system' => $sourceSystem,
                'name' => $orderData['customer']['name'],
                'email' => $orderData['customer']['email'] ?? null,
                'phone' => $orderData['customer']['phone'] ?? null,
                'address' => $orderData['customer']['address'] ?? null,
            ]);

            $destination = $this->entityService->findOrCreateDestination([
                'external_id' => $orderData['destination']['external_id'],
                'source_system' => $sourceSystem,
                'name' => $orderData['destination']['name'],
                'address' => $orderData['destination']['address'] ?? null,
                'postal_code' => $orderData['destination']['postal_code'] ?? null,
                'city' => $orderData['destination']['city'] ?? null,
                'state' => $orderData['destination']['state'] ?? null,
            ]);

            $carrier = $this->entityService->findOrCreateCarrier([
                'external_id' => $orderData['carrier']['external_id'],
                'source_system' => $sourceSystem ?? null,
                'name' => $orderData['carrier']['name'],
                'document' => $orderData['carrier']['document'] ?? null,
                'contact_phone' => $orderData['carrier']['phone'] ?? null,
            ]);

            $vehicle = $this->entityService->findOrCreateVehicle([
                'external_id' => $orderData['vehicle']['external_id'] ?? null,
                'source_system' => $sourceSystem ?? null,
                'vehiclePlate' => $orderData['vehicle']['vehiclePlate'],
                'model' => $orderData['vehicle']['model'] ?? null,
                'carrier_id' => $carrier->id,
            ]);

            //dd($orderData['driver']);

            $driver = $this->entityService->findOrCreateDriver([
                'external_id' => $orderData['driver']['external_id'] ?? null,
                'source_system' => $sourceSystem ?? null,
                'name' => $orderData['driver']['name'],
                'document' => $orderData['driver']['document'] ?? null,
                'phone' => $orderData['driver']['phone'] ?? null,
                'carrier_id' => $carrier->id,
            ]);

            $order = LoadingOrder::query()->create([
                'external_id' => $orderData['external_id'] ?? null,
                'observations' => $orderData['notes'] ?? null,
                'source_system' => $sourceSystem ?? null,
                'issue_date' => $orderData['issue_date'],
                'status' => $orderData['status'] ?? 'pending',
                'customer_id' => $customer->id,
                'destination_id' => $destination->id,
                'carrier_id' => $carrier->id,
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
            ]);

            foreach ($orderData['items'] as $item) {
                $product = $this->entityService->findOrCreateProduct([
                    'product_sku' => $item['product_sku'],
                    'description' => $item['product_description'],
                    'unit' => $item['unit'] ?? 'un',
                ]);

                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                ]);

                if (!empty($item['packages']) && is_array($item['packages'])) {
                    foreach ($item['packages'] as $package) {
                        $orderItem->packages()->firstOrCreate(
                            ['unique_package_code' => $package['unique_package_code']],
                            ['quantity_in_package' => $package['quantity_in_package'] ?? 1]
                        );
                    }
                }
            }

            DB::commit();

            $this->logService->log(
                self::ENDPOINT,
                $payload,
                HttpStatus::CREATED->value,
                null
            );

            return $order;
        } catch (IntegrationException $e) {
            DB::rollBack();
            $this->LogError($payload, $e->getHttpStatus(), $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro inesperado na integração de ordem de carregamento', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->LogError($payload, HttpStatus::INTERNAL_SERVER_ERROR->value, $e->getMessage());
            throw new IntegrationException(
                'Erro interno ao processar a integração',
                HttpStatus::INTERNAL_SERVER_ERROR->value
            );
        }
    }

    /**
     * @throws IntegrationException
     */
    private function validateData(array $payload): void
    {
        if (empty($payload['source_system'])) {
            throw new IntegrationException(
                'Sistema de origem não informado',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($payload['loadingOrder'])) {
            throw new IntegrationException(
                'Dados da ordem de carregamento não informados',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        $orderData = $payload['loadingOrder'];

        if (empty($orderData['external_id'])) {
            throw new IntegrationException(
                'ID externo da ordem de carregamento não informado',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($orderData['issue_date'])) {
            throw new IntegrationException(
                'Data de emissão da ordem de carregamento não informada',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($orderData['customer']['external_id'])) {
            throw new IntegrationException(
                'ID externo do cliente não informado',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($orderData['destination']['external_id'])) {
            throw new IntegrationException(
                'ID externo do destino não informado',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($orderData['carrier']['external_id'])) {
            throw new IntegrationException(
                'ID externo do transportador não informado',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($orderData['vehicle']['external_id'])) {
            throw new IntegrationException(
                'ID externo do veículo não informado',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($orderData['driver']['external_id'])) {
            throw new IntegrationException(
                'ID externo do motorista não informado',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($orderData['items']) || !is_array($orderData['items'])) {
            throw new IntegrationException(
                'Itens da ordem de carregamento não informados ou formato inválido',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }
    }

    private function logError(array $payload, int $httpStatus, string $message): void
    {
        $this->logService->log(
            self::ENDPOINT,
            $payload,
            $httpStatus,
            $message
        );
    }

}
