<?php

namespace App\DTOs\Integration;

use App\DTOs\Entity\UserDTO;

readonly class UserIntegrationDTO
{
    public function __construct(
        public string  $sourceSystem,
        public UserDTO $user,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sourceSystem: $data['source_system'],
            user: UserDTO::fromArray($data['user'], $data['source_system']),
        );
    }
}
