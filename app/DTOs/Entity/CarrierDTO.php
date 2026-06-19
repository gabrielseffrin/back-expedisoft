<?php

namespace App\DTOs\Entity;

readonly class CarrierDTO
{
    public function __construct(
        public string  $externalId,
        public string  $name,
        public ?string $sourceSystem = null,
        public ?string $document = null,
        public ?string $contactPhone = null,
    ) {
    }

    public static function fromArray(array $data, ?string $sourceSystem = null): self
    {
        return new self(
            externalId: $data['external_id'],
            name: $data['name'],
            sourceSystem: $data['source_system'] ?? $sourceSystem,
            document: $data['document'] ?? null,
            contactPhone: $data['phone'] ?? $data['contact_phone'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id'   => $this->externalId,
            'source_system' => $this->sourceSystem,
            'name'          => $this->name,
            'document'      => $this->document,
            'contact_phone' => $this->contactPhone,
        ];
    }
}
