<?php

namespace App\DTOs\Entity;

readonly class DockDTO
{
    public function __construct(
        public string  $externalId,
        public string  $dockCode,
        public ?string $sourceSystem = null,
        public ?string $description = null,
        public ?string $location = null,
    ) {
    }

    public static function fromArray(array $data, ?string $sourceSystem = null): self
    {
        return new self(
            externalId: $data['external_id'],
            dockCode: $data['dock_code'],
            sourceSystem: $data['source_system'] ?? $sourceSystem,
            description: $data['description'] ?? null,
            location: $data['location'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id'   => $this->externalId,
            'source_system' => $this->sourceSystem,
            'dock_code'     => $this->dockCode,
            'description'   => $this->description,
            'location'      => $this->location,
        ];
    }
}
