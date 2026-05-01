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
            $photo = $this->orderPhotoService->store(auth()->user(), $request, $orderId);

            return response()->json([
                'success' => true,
                'message' => 'Foto recebida e entrou na fila de processamento.',
                'data' => [
                    'photo_id' => $photo->id,
                    'loading_order_id' => $photo->loading_order_id,
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
