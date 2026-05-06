<?php

namespace App\Http\Controllers;

use App\Services\DockService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Docks", description: "Gerenciamento de docas")]
class DockController extends Controller
{

    public function __construct(private readonly DockService $dockService)
    {
    }

    #[OA\Get(
        path: "/api/docks",
        summary: "Retorna a lista de docas",
        security: [["bearerAuth" => []]],
        tags: ["Docks"],
        responses: [
            new OA\Response(response: 200, description: "Docas listadas com sucesso")
        ]
    )]
    public function getAllDocks(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->dockService->getAllDocks();
    }
}
