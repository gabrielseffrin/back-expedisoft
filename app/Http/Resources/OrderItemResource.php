<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'note' => $this->note,

            'product' => [
                'id' => $this->product?->id,
                'description' => $this->product?->description,
                'sku' => $this->product?->sku,
                'unit' => $this->product?->unit,
                'weight' => $this->product?->weight,
            ],

            'packages' => PackageResource::collection(
                $this->whenLoaded('packages')
            ),
        ];
    }
}
