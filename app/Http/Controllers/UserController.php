<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Users", description: "Gerenciamento de usuários do sistema")]
class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    #[OA\Get(
        path: "/api/operators",
        summary: "Retorna a lista de operadores",
        security: [["bearerAuth" => []]],
        tags: ["Users"],
        responses: [
            new OA\Response(response: 200, description: "Operadores listados com sucesso")
        ]
    )]
    public function getOperators(): JsonResponse
    {
        return response()->json($this->userService->getOperators(), 200);
    }
}
