<?php

namespace App\Http\Controllers\Integration;

use App\Exceptions\IntegrationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\OrderIntegrationRequest;
use App\Services\LoadingOrderIntegrationService;

use OpenApi\Attributes as OA;

class LoadingOrderController extends Controller
{
    public function __construct(private readonly LoadingOrderIntegrationService $service)
    {
    }

    #[OA\Post(
        path: "/api/integration/order",
        description: "Endpoint responsável por receber o payload completo de uma ordem de carga, incluindo cliente, destino, transportadora, veículo, motorista e itens.",
        summary: "Sincroniza uma nova ordem de carga",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["source_system", "loadingOrder"],
                properties: [
                    new OA\Property(property: "source_system", type: "string", example: "ERP_SISTEMA_A"),
                    new OA\Property(
                        property: "loadingOrder",
                        required: ["external_id", "issue_date", "status", "customer", "destination", "carrier", "vehicle", "driver", "items"],
                        properties: [
                            new OA\Property(property: "external_id", type: "string", example: "DOC123456"),
                            new OA\Property(property: "issue_date", type: "string", format: "date", example: "2025-01-07"),
                            new OA\Property(property: "delivery_date", type: "string", format: "date", example: "2025-01-10", nullable: true),
                            new OA\Property(property: "status", type: "string", enum: ["pending", "in_progress", "completed", "cancelled"], example: "pending"),
                            new OA\Property(property: "notes", type: "string", example: "Entregar no período da manhã", nullable: true),

                            // Customer
                            new OA\Property(property: "customer", properties: [
                                new OA\Property(property: "external_id", type: "string", example: "CUST-99"),
                                new OA\Property(property: "name", type: "string", example: "Cliente Exemplo Ltda"),
                                new OA\Property(property: "email", type: "string", example: "contato@cliente.com"),
                                new OA\Property(property: "phone", type: "string", example: "11999999999"),
                                new OA\Property(property: "address", type: "string", example: "Rua das Flores, 123")
                            ], type: "object"),

                            // Destination
                            new OA\Property(property: "destination", properties: [
                                new OA\Property(property: "external_id", type: "string", example: "DEST-45"),
                                new OA\Property(property: "name", type: "string", example: "CD Regional Sul"),
                                new OA\Property(property: "address", type: "string", example: "Av. Industrial, 500"),
                                new OA\Property(property: "city", type: "string", example: "Porto Alegre"),
                                new OA\Property(property: "state", type: "string", example: "RS"),
                                new OA\Property(property: "postal_code", type: "string", example: "90000000")
                            ], type: "object"),

                            // Items
                            new OA\Property(property: "items", type: "array", items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "product_sku", type: "string", example: "SKU-001"),
                                    new OA\Property(property: "product_description", type: "string", example: "Caixa de Parafusos Aço"),
                                    new OA\Property(property: "quantity", type: "integer", example: 10),
                                    new OA\Property(property: "unit", type: "string", example: "UN")
                                ]
                            ))
                        ],
                        type: "object"
                    )
                ]
            )
        ),
        tags: ["Integration"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Ordem de carga processada com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Payload received successfully")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Erro de validação nos dados enviados"),
            new OA\Response(response: 500, description: "Erro interno no servidor")
        ]
    )]
    /**
     * @throws \Exception
     */
    public function storeOrder(OrderIntegrationRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $order = $this->service->storeOrder($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payload received successfully',
                'data' => $order
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
