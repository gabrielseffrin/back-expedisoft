<?php

namespace App\DTOs\Entity;

readonly class VehicleDTO
{
    public function __construct(
        public string  $vehiclePlate,
        public string  $carrierId,
        public ?string $externalId = null,
        public ?string $sourceSystem = null,
        public ?string $model = null,
    ) {
    }

    public static function fromArray(array $data, ?string $sourceSystem = null): self
    {
        return new self(
            vehiclePlate: $data['vehiclePlate'],
            carrierId: $data['carrier_id'],
            externalId: $data['external_id'] ?? null,
            sourceSystem: $data['source_system'] ?? $sourceSystem,
            model: $data['model'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id'   => $this->externalId,
            'source_system' => $this->sourceSystem,
            'vehiclePlate'  => $this->vehiclePlate,
            'model'         => $this->model,
            'carrier_id'    => $this->carrierId,
        ];
    }
}
