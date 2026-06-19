<?php

namespace App\DTOs\Integration;

use App\DTOs\Entity\ProductDTO;

readonly class OrderItemIntegrationDTO
{
    /**
     * @param PackageIntegrationDTO[] $packages
     */
    public function __construct(
        public ProductDTO $product,
        public int        $quantity,
        public array      $packages = [],
    ) {
    }

    public static function fromArray(array $item): self
    {
        $packages = array_map(
            fn (array $package) => PackageIntegrationDTO::fromArray($package),
            $item['packages'] ?? []
        );

        return new self(
            product: ProductDTO::fromArray([
                'product_sku'         => $item['product_sku'],
                'description'         => $item['product_description'],
                'unit'                => $item['unit'] ?? 'un',
                'weight'              => $item['weight'] ?? null,
                'barcode'             => $item['barcode'] ?? null,
            ]),
            quantity: $item['quantity'],
            packages: $packages,
        );
    }
}
