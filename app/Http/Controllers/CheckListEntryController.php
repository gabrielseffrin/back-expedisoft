<?php

namespace App\Http\Controllers;

use App\Models\ChecklistEntry;
use App\Models\LoadingOrder;
use App\Services\CheckListEntryService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Checklist", description: "Gerenciamento de conferência de pacotes")]
class CheckListEntryController extends Controller
{
    public function __construct(private readonly CheckListEntryService $checklistEntry)
    {
    }

    #[OA\Post(
        path: "/api/order/{orderId}/checklist",
        summary: "Realiza a conferência (scan) de um pacote",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["qr_code"],
                properties: [
                    new OA\Property(property: "qr_code", description: "Código do pacote lido no QRCode/Código de barras", type: "string")
                ]
            )
        ),
        tags: ["Checklist"],
        parameters: [
            new OA\Parameter(
                name: "orderId",
                description: "ID da Ordem de Carregamento",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Pacote conferido com sucesso"),
            new OA\Response(response: 400, description: "Erro ao conferir pacote")
        ]
    )]
    public function store(Request $request, $orderId): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        try {
            $checklistEntry = $this->checklistEntry->store(
                auth()->user(),
                $orderId,
                $request->input('qr_code')
            );

            return response()->json([
                'success' => true,
                'message' => 'Pacote conferido com sucesso',
                'data' => $checklistEntry,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
