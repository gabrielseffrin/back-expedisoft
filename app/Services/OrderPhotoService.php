<?php

namespace App\Services;

use App\Http\Requests\OrderPhotoRequest;
use App\Jobs\UploadPhotoToDriveJob;
use App\Models\LoadingOrder;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OrderPhotoService
{

    /**
     * @throws AuthorizationException
     */
    public function store(User $operator, OrderPhotoRequest $request, string $orderId): Photo
    {
        $order = LoadingOrder::query()->find($orderId);

        if (!$order) {
            throw new ModelNotFoundException('Ordem de carregamento não encontrada.');
        }

        if ($order->operator_id !== $operator->id) {
            throw new AuthorizationException('Acesso negado.');
        }

        if ($order->status !== 'in_progress') {
            throw new BadRequestException('A ordem precisa estar em andamento para anexar fotos.');
        }

        $photoFile = $request->file('photo');
        $localPath = $photoFile->store('temp_photos', 'local');

        $photo = new Photo();
        $photo->uploaded_by = $operator->id;
        $photo->loading_order_id = $order->id;
        $photo->storage_path = 'Processando...';
        $photo->mime = $photoFile->getClientMimeType();
        $photo->status = Photo::STATUS_PENDING;
        $photo->save();

        $folderName = 'Cargas/' . $order->external_id;
        UploadPhotoToDriveJob::dispatch($photo->id, $localPath, $folderName);

        return $photo;
    }
}
