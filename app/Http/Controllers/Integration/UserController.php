<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\UserIntegrationRequest;
use App\Services\UserService;

class UserController extends Controller
{

    public function __construct(private readonly UserService $service){}


    /**
     * @throws \Exception
     */
    public function storeUser(UserIntegrationRequest $request): \Illuminate\Http\JsonResponse
    {
        $order = $this->service->storeOrder($request->validated());

        return response()->json([
            'message' => 'User created or updated successfully',
            'data' => null
        ], 201);
    }
}
