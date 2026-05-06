<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Health", description: "Verificação de integridade do sistema")]
class HealthController extends Controller
{
    #[OA\Get(
        path: "/api/health",
        summary: "Verifica a integridade da API e seus serviços",
        tags: ["Health"],
        responses: [
            new OA\Response(response: 200, description: "Serviços funcionando corretamente"),
            new OA\Response(response: 503, description: "Um ou mais serviços com falha")
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $db = $this->checkDatabase();

        $status = $db === 'ok' ? 'ok' : 'degraded';
        $httpCode = $db === 'ok' ? 200 : 503;

        return response()->json([
            'status'    => $status,
            'services'  => [
                'database' => $db,
                'queue'    => $this->checkQueue(),
            ],
            'timestamp' => now()->toIso8601String(),
        ], $httpCode);
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }

    private function checkQueue(): string
    {
        try {
            return Queue::size() >= 0 ? 'ok' : 'error';
        } catch (\Throwable) {
            return 'error';
        }
    }
}
