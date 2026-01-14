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
    public function login(Request $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        $authService = new AuthService();
        try {
            $data = $authService->loginService($request);
            return response()->json(['token' => $data], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
