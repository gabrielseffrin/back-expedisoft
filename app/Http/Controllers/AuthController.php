<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Psy\Util\Json;

class AuthController extends Controller
{

    public function __construct(private readonly AuthService $authService){}

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
