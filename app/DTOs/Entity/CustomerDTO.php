<?php

namespace App\DTOs\Entity;

readonly class CustomerDTO
{
    public function __construct(
        public string  $externalId,
        public string  $name,
        public ?string $sourceSystem = null,
        public ?string $document = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
    ) {
    }

    public static function fromArray(array $data, ?string $sourceSystem = null): self
    {
        return new self(
            externalId: $data['external_id'],
            name: $data['name'],
            sourceSystem: $data['source_system'] ?? $sourceSystem,
            document: $data['document'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id'   => $this->externalId,
            'source_system' => $this->sourceSystem,
            'name'          => $this->name,
            'document'      => $this->document,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'address'       => $this->address,
        ];
    }
}
