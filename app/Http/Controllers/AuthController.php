<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Psy\Util\Json;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{

    #[OA\Post(
        path: "/api/login",
        description: "Endpoint para autenticação de usuários e geração de tokens de acesso.",
        summary: "Autenticação de usuário",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "email@teste.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "senha123")
                ])),
        tags: ["Authentication"],
        responses: [
            new OA\Response(response: 200, description: "Autenticação bem-sucedida, token gerado"),
            new OA\Response(response: 401, description: "Credenciais inválidas"),
            new OA\Response(response: 500, description: "Erro interno do servidor")
        ])
    ]
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function login(Request $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->authService->loginService($request);
            return response()->json(['token' => $data], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function logout(Request $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        $this->authService->logoutService($request->user());

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function me(Request $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        return response()->json($request->user(), 200);
    }
}
