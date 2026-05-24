<?php

namespace App\DTOs\Entity;

readonly class DestinationDTO
{
    public function __construct(
        public string  $externalId,
        public string  $name,
        public ?string $sourceSystem = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postalCode = null,
    ) {
    }

    public static function fromArray(array $data, ?string $sourceSystem = null): self
    {
        return new self(
            externalId: $data['external_id'],
            name: $data['name'],
            sourceSystem: $data['source_system'] ?? $sourceSystem,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postalCode: $data['postal_code'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id'   => $this->externalId,
            'source_system' => $this->sourceSystem,
            'name'          => $this->name,
            'address'       => $this->address,
            'city'          => $this->city,
            'state'         => $this->state,
            'postal_code'   => $this->postalCode,
        ];
    }
}
