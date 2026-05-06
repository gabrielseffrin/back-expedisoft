<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = null;
        if (!empty($this->drive_id)) {
            $template = config('services.google_drive.file_url', 'https://drive.google.com/uc?id={id}');
            $url = str_replace('{id}', $this->drive_id, $template);
        }

        return [
            'id' => $this->id,
            'loading_order_id' => $this->loading_order_id,
            'storage_path' => $this->storage_path,
            'drive_id' => $this->drive_id,
            'mime' => $this->mime,
            'status' => $this->status,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_at' => $this->uploaded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'url' => $url,
        ];
    }
}
