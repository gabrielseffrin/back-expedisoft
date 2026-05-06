<?php

namespace App\Http\Controllers\Integration;

use App\Exceptions\IntegrationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\UserIntegrationRequest;
use App\Jobs\ProcessUserJob;
use Exception;
use Illuminate\Http\JsonResponse;

use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Post(
        path: "/api/integration/user",
        summary: "Create or update a user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["source_system", "user"],
                properties: [
                    new OA\Property(property: "source_system", type: "string", example: "SAP"),
                    new OA\Property(
                        property: "user",
                        required: ["external_id", "name", "email"],
                        properties: [
                            new OA\Property(property: "external_id", type: "string", example: "12345"),
                            new OA\Property(property: "name", type: "string", example: "John Doe"),
                            new OA\Property(property: "email", type: "string", example: "teste@email.com"),
                        ],
                        type: "object"
                    )
                ]
            )
        ),
        tags: ["Integration"],
        responses: [
            new OA\Response(response: 202, description: "Payload received and queued for processing"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Internal server error")
        ]
    )]
    /**
     * @param UserIntegrationRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function storeUser(UserIntegrationRequest $request): JsonResponse
    {
        try {
            ProcessUserJob::dispatch($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payload received and queued for processing'
            ], 202);

        } catch (IntegrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->getErrors()
            ], $e->getHttpStatus());

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
