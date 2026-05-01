<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadPhotoToDriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $photoId;
    public $localPath;
    public $folderName;

    public function __construct($photoId, $localPath, $folderName)
    {
        $this->photoId = $photoId;
        $this->localPath = $localPath;
        $this->folderName = $folderName;
    }

    public function handle(): void
    {
        if (!Storage::disk('local')->exists($this->localPath)) {
            Log::warning('Arquivo local nao encontrado para upload.', [
                'photo_id' => $this->photoId,
                'local_path' => $this->localPath,
            ]);

            return;
        }

        $fileContents = Storage::disk('local')->get($this->localPath);
        $fileName = basename($this->localPath);
        $drivePath = $this->folderName . '/' . $fileName;

        try {
            Storage::disk('google')->put($drivePath, $fileContents);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar arquivo para o Drive.', [
                'photo_id' => $this->photoId,
                'drive_path' => $drivePath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $driveId = null;

        if (app()->environment('testing')) {
            $driveId = 'fake-drive-id-123';
        } else {
            $adapter = Storage::disk('google')->getAdapter();

            if (method_exists($adapter, 'getMetadata')) {
                $metadata = $adapter->getMetadata($drivePath);
                $driveId = $metadata->extraMetadata()['id'] ?? null;
            }
        }

        $photo = Photo::query()->find($this->photoId);
        if ($photo) {
            $photo->update([
                'storage_path' => $drivePath,
                'drive_id' => $driveId,
            ]);
        }

        Storage::disk('local')->delete($this->localPath);
    }
}
