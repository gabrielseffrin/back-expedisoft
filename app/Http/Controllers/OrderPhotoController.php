<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderPhotoRequest;
use App\Services\OrderPhotoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OrderPhotoController extends Controller
{
    public function __construct(private readonly OrderPhotoService $orderPhotoService)
    {
    }

    public function store(OrderPhotoRequest $request, $orderId): \Illuminate\Http\JsonResponse
    {
        try {
            $photos = $this->orderPhotoService->store(auth()->user(), $request, $orderId);
            $photoIds = array_map(static fn ($photo) => $photo->id, $photos);

            return response()->json([
                'success' => true,
                'message' => 'Fotos recebidas e entraram na fila de processamento.',
                'data' => [
                    'photo_ids' => $photoIds,
                    'loading_order_id' => $photos[0]->loading_order_id,
                    'count' => count($photoIds),
                ],
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ordem de carregamento não encontrada.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (BadRequestException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error("Erro ao anexar foto: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao anexar foto.'], 500);
        }
    }
}
