<?php

namespace App\DTOs\Entity;

readonly class UserDTO
{
    public function __construct(
        public string  $name,
        public string  $email,
        public ?string $externalId = null,
        public ?string $sourceSystem = null,
    ) {
    }

    public static function fromArray(array $data, ?string $sourceSystem = null): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            externalId: $data['external_id'] ?? null,
            sourceSystem: $data['source_system'] ?? $sourceSystem,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id'   => $this->externalId,
            'source_system' => $this->sourceSystem,
            'name'          => $this->name,
            'email'         => $this->email,
        ];
    }
}
