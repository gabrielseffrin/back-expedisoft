<?php

namespace App\Http\Controllers\Integration;

use App\Exceptions\IntegrationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\DockIntegrationRequest;
use App\Jobs\ProcessDockJob;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Integration - Docks", description: "Integração de Docas")]
class DockController extends Controller
{
    #[OA\Post(
        path: "/api/integration/dock",
        summary: "Recebe e enfileira o payload de integração de docas",
        security: [["ApiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                description: "Payload da integração",
                type: "object"
            )
        ),
        tags: ["Integration - Docks"],
        responses: [
            new OA\Response(response: 202, description: "Payload recebido e enfileirado"),
            new OA\Response(response: 500, description: "Erro ao processar integração")
        ]
    )]
    public function storeDock(DockIntegrationRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            ProcessDockJob::dispatch($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payload received and queued for processing'
            ], 202);

        } catch (IntegrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
