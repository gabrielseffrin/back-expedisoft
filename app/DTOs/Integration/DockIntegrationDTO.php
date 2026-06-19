<?php

namespace App\DTOs\Integration;

use App\DTOs\Entity\DockDTO;

readonly class DockIntegrationDTO
{
    public function __construct(
        public string  $sourceSystem,
        public DockDTO $dock,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sourceSystem: $data['source_system'],
            dock: DockDTO::fromArray($data['dock'], $data['source_system']),
        );
    }
}
