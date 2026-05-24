<?php

namespace App\DTOs\Integration;

readonly class PackageIntegrationDTO
{
    public function __construct(
        public string $uniquePackageCode,
        public int    $quantityInPackage = 1,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uniquePackageCode: $data['unique_package_code'],
            quantityInPackage: $data['quantity_in_package'] ?? 1,
        );
    }
}
