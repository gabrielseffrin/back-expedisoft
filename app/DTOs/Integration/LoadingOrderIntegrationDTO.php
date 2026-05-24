<?php

namespace App\DTOs\Integration;

use App\DTOs\Entity\CarrierDTO;
use App\DTOs\Entity\CustomerDTO;
use App\DTOs\Entity\DestinationDTO;
use App\DTOs\Entity\DriverDTO;
use App\DTOs\Entity\VehicleDTO;

readonly class LoadingOrderIntegrationDTO
{
    /**
     * @param OrderItemIntegrationDTO[] $items
     */
    public function __construct(
        public string         $sourceSystem,
        public string         $externalId,
        public string         $issueDate,
        public string         $status,
        public CustomerDTO    $customer,
        public DestinationDTO $destination,
        public CarrierDTO     $carrier,
        public VehicleDTO     $vehicle,
        public DriverDTO      $driver,
        public array          $items,
        public ?string        $deliveryDate = null,
        public ?string        $notes = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $sourceSystem = $data['source_system'];
        $orderData    = $data['loadingOrder'];

        $carrier = CarrierDTO::fromArray($orderData['carrier'], $sourceSystem);

        $vehicle = new VehicleDTO(
            vehiclePlate: $orderData['vehicle']['vehiclePlate'],
            carrierId: '',        // preenchido pelo service após persistência da carrier
            externalId: $orderData['vehicle']['external_id'] ?? null,
            sourceSystem: $sourceSystem,
            model: $orderData['vehicle']['model'] ?? null,
        );

        $driver = new DriverDTO(
            name: $orderData['driver']['name'],
            carrierId: '',        // preenchido pelo service após persistência da carrier
            externalId: $orderData['driver']['external_id'] ?? null,
            sourceSystem: $sourceSystem,
            document: $orderData['driver']['document'] ?? null,
            phone: $orderData['driver']['phone'] ?? null,
        );

        $items = array_map(
            fn (array $item) => OrderItemIntegrationDTO::fromArray($item),
            $orderData['items']
        );

        return new self(
            sourceSystem: $sourceSystem,
            externalId: $orderData['external_id'],
            issueDate: $orderData['issue_date'],
            status: $orderData['status'] ?? 'pending',
            customer: CustomerDTO::fromArray($orderData['customer'], $sourceSystem),
            destination: DestinationDTO::fromArray($orderData['destination'], $sourceSystem),
            carrier: $carrier,
            vehicle: $vehicle,
            driver: $driver,
            items: $items,
            deliveryDate: $orderData['delivery_date'] ?? null,
            notes: $orderData['notes'] ?? null,
        );
    }
}
