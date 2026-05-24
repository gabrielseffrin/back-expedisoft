<?php

namespace App\DTOs\Entity;

readonly class ProductDTO
{
    public function __construct(
        public string  $sku,
        public string  $description,
        public ?string $unit = 'un',
        public ?float  $weight = null,
        public ?string $barcode = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sku: $data['product_sku'],
            description: $data['description'] ?? $data['product_description'],
            unit: $data['unit'] ?? 'un',
            weight: $data['weight'] ?? null,
            barcode: $data['barcode'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'product_sku' => $this->sku,
            'description' => $this->description,
            'unit'        => $this->unit,
            'weight'      => $this->weight,
            'barcode'     => $this->barcode,
        ];
    }
}
