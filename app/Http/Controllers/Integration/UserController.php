<?php

namespace App\Http\Controllers\Integration;

use App\Exceptions\IntegrationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\UserIntegrationRequest;
use App\Services\UserService;

class UserController extends Controller
{

    public function __construct(
        private readonly UserService $userService
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function storeUser(UserIntegrationRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $this->userService->storeUser($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'User created or updated successfully',
                //'data' => $user
            ], 201);

        } catch (IntegrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], $e->getHttpStatus());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
