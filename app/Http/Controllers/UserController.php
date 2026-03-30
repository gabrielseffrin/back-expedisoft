<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function getOperators(): JsonResponse
    {
        return response()->json($this->userService->getOperators(), 200);
    }
}
