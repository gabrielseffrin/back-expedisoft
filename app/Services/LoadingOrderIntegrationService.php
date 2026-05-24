<?php

namespace App\Services;

use App\DTOs\Entity\VehicleDTO;
use App\DTOs\Entity\DriverDTO;
use App\DTOs\Integration\LoadingOrderIntegrationDTO;
use App\Enums\HttpStatus;
use App\Exceptions\IntegrationException;
use App\Models\LoadingOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class LoadingOrderIntegrationService
{
    private const ENDPOINT = '/api/integration/order';

    public function __construct(private EntityService $entityService, private IntegrationLogService $logService)
    {
    }

    /**
     * @param LoadingOrderIntegrationDTO $dto
     * @return LoadingOrder
     * @throws IntegrationException|\Exception
     */
    public function storeOrder(LoadingOrderIntegrationDTO $dto): LoadingOrder
    {
        DB::beginTransaction();
        try {
            $customer    = $this->entityService->findOrCreateCustomer($dto->customer);
            $destination = $this->entityService->findOrCreateDestination($dto->destination);
            $carrier     = $this->entityService->findOrCreateCarrier($dto->carrier);

            // Injeta o carrier_id nos DTOs de veículo e motorista
            $vehicleDto = new VehicleDTO(
                vehiclePlate: $dto->vehicle->vehiclePlate,
                carrierId: $carrier->id,
                externalId: $dto->vehicle->externalId,
                sourceSystem: $dto->vehicle->sourceSystem,
                model: $dto->vehicle->model,
            );

            $driverDto = new DriverDTO(
                name: $dto->driver->name,
                carrierId: $carrier->id,
                externalId: $dto->driver->externalId,
                sourceSystem: $dto->driver->sourceSystem,
                document: $dto->driver->document,
                phone: $dto->driver->phone,
            );

            $vehicle = $this->entityService->findOrCreateVehicle($vehicleDto);
            $driver  = $this->entityService->findOrCreateDriver($driverDto);

            $order = LoadingOrder::query()->create([
                'external_id'    => $dto->externalId,
                'observations'   => $dto->notes ?? null,
                'source_system'  => $dto->sourceSystem ?? null,
                'issue_date'     => $dto->issueDate,
                'status'         => $dto->status,
                'customer_id'    => $customer->id,
                'destination_id' => $destination->id,
                'carrier_id'     => $carrier->id,
                'vehicle_id'     => $vehicle->id,
                'driver_id'      => $driver->id,
            ]);

            foreach ($dto->items as $itemDto) {
                $product   = $this->entityService->findOrCreateProduct($itemDto->product);
                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $itemDto->quantity,
                ]);

                foreach ($itemDto->packages as $packageDto) {
                    $orderItem->packages()->firstOrCreate(
                        ['unique_package_code'   => $packageDto->uniquePackageCode],
                        ['quantity_in_package'   => $packageDto->quantityInPackage]
                    );
                }
            }

            DB::commit();

            $this->logService->log(
                self::ENDPOINT,
                $this->dtoToLogArray($dto),
                HttpStatus::CREATED->value,
                null
            );

            return $order;

        } catch (IntegrationException $e) {
            DB::rollBack();
            $this->logError($this->dtoToLogArray($dto), $e->getHttpStatus(), $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro inesperado na integração de ordem de carregamento', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->logError($this->dtoToLogArray($dto), HttpStatus::INTERNAL_SERVER_ERROR->value, $e->getMessage());
            throw new IntegrationException(
                'Erro interno ao processar a integração',
                HttpStatus::INTERNAL_SERVER_ERROR->value
            );
        }
    }

    private function dtoToLogArray(LoadingOrderIntegrationDTO $dto): array
    {
        return [
            'source_system' => $dto->sourceSystem,
            'external_id'   => $dto->externalId,
        ];
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
