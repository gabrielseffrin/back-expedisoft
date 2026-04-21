<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
            'unique_package_code' => $this->unique_package_code,
            'quantity_in_package' => $this->quantity_in_package,
            'status' => $this->checklistEntry ? 'checked' : 'unchecked',

            'checked_at' => $this->checklistEntry?->scanned_at,

            'checked_by' => $this->checklistEntry?->scannedBy?->name,
        ];
    }
}
