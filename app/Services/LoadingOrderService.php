<?php

namespace App\Services;


use App\Models\LoadingOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoadingOrderService
{

    protected EntityService $entityService;

    public function __construct(EntityService $entityService)
    {
        $this->entityService = $entityService;
    }

    /**
     * @throws \Exception
     */
    public function storeOrder(array $payload)
    {
        DB::beginTransaction();
        try {
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
                'address' => $orderData['destination']['address'],
                'postal_code' => $orderData['destination']['postal_code'],
                'city' => $orderData['destination']['city'],
                'state' => $orderData['destination']['state'],
            ]);

            $carrier = $this->entityService->findOrCreateCarrier([
                'external_id' => $orderData['carrier']['external_id'] ?? null,
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
                    'sku' => $item['product_sku'],
                    'description' => $item['product_description'],
                    'weight' => $item['weight'] ?? null,
                    'unit' => $item['unit'] ?? 'un',
                    'barcode' => $item['barcode'] ?? null,
                ]);

                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'note' => $item['note'] ?? null,
                ]);

                if (!empty($item['unique_package_code'])) {
                    $orderItem->packages()->create([
                        'unique_package_code' => $item['unique_package_code'],
                        'quantity_in_package' => $item['quantity_in_package'] ?? $item['quantity'],
                    ]);
                }
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



}
