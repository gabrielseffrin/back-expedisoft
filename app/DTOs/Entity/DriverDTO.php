<?php

namespace App\DTOs\Entity;

readonly class DriverDTO
{
    public function __construct(
        public string  $name,
        public string  $carrierId,
        public ?string $externalId = null,
        public ?string $sourceSystem = null,
        public ?string $document = null,
        public ?string $phone = null,
    ) {
    }

    public static function fromArray(array $data, ?string $sourceSystem = null): self
    {
        return new self(
            name: $data['name'],
            carrierId: $data['carrier_id'],
            externalId: $data['external_id'] ?? null,
            sourceSystem: $data['source_system'] ?? $sourceSystem,
            document: $data['document'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id'   => $this->externalId,
            'source_system' => $this->sourceSystem,
            'name'          => $this->name,
            'document'      => $this->document,
            'phone'         => $this->phone,
            'carrier_id'    => $this->carrierId,
        ];
    }
}
