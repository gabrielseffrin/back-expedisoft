<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoadingOrderResource extends JsonResource
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
            'external_id' => $this->external_id,
            'issue_date' => $this->issue_date,
            'status' => $this->status,
            'customer' => $this->customer?->name,
            'destination' => $this->destination?->name,
            'carrier' => $this->carrier?->name,
            'driver' => $this->driver?->name,
            'vehicle' => $this->vehicle?->vehiclePlate,
            'operator' => $this->operator?->name,
            'dock' => $this->dock?->name,
            'justification' => $this->justification,
            'observations' => $this->observations,
            'scheduled_at' => $this->scheduled_at,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,

            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
