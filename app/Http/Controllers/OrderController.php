<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleOrderRequest;
use App\Http\Resources\LoadingOrderResource;
use App\Services\OrderService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Orders", description: "Gerenciamento e controle de Ordens de Carregamento")]
class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    #[OA\Get(
        path: "/api/order/{orderId}",
        description: "Retorna os detalhes de uma ordem se o ID for passado. Se não, retorna uma lista paginada com todas as ordens.",
        summary: "Busca uma ordem específica ou lista todas as ordens",
        security: [["bearerAuth" => []]],
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(
                name: "orderId",
                description: "UUID da ordem de carregamento",
                in: "path",
                required: false,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Operação realizada com sucesso"),
            new OA\Response(response: 404, description: "Ordem de carregamento não encontrada")
        ]
    )]
    public function getOrder($orderId = null): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|LoadingOrderResource
    {
        try {
            if ($orderId) {
                $order = $this->orderService->findOrderById($orderId);
                return new LoadingOrderResource($order);
            }

            $orders = $this->orderService->getAllOrders(10);
            return LoadingOrderResource::collection($orders);

        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Ordem de carregamento não encontrada.'], 404);
        }
    }

    #[OA\Get(
        path: "/api/order/my-orders",
        description: "Retorna uma lista paginada de ordens criadas por ou designadas para o operador atual.",
        summary: "Lista as ordens atreladas ao usuário logado",
        security: [["bearerAuth" => []]],
        tags: ["Orders"],
        responses: [
            new OA\Response(response: 200, description: "Operação realizada com sucesso"),
            new OA\Response(response: 404, description: "Nenhuma ordem encontrada para o usuário")
        ]
    )]
    public function getMyOrders(): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $orders = $this->orderService->getOrdersByCurrentUser(10, auth()->user());
            return LoadingOrderResource::collection($orders);

        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Nenhuma ordem encontrada para o usuário atual.'], 404);
        }
    }

    #[OA\Post(
        path: "/api/order/schedule",
        summary: "Agenda uma ordem de carregamento",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["id", "scheduled_at", "status"],
                properties: [
                    new OA\Property(property: "id", description: "ID da Ordem", type: "string"),
                    new OA\Property(property: "scheduled_at", description: "Data/hora do agendamento", type: "string", format: "date-time"),
                    new OA\Property(property: "status", description: "Status alvo (ex: scheduled)", type: "string"),
                    new OA\Property(property: "dock_id", description: "ID da Doca (opcional)", type: "string"),
                    new OA\Property(property: "operator_id", description: "ID do Operador (opcional)", type: "string")
                ]
            )
        ),
        tags: ["Orders"],
        responses: [
            new OA\Response(response: 200, description: "Ordem agendada com sucesso"),
            new OA\Response(response: 403, description: "O usuário selecionado não possui permissão de operador"),
            new OA\Response(response: 404, description: "Ordem de carregamento não encontrada"),
            new OA\Response(response: 500, description: "Erro interno no servidor")
        ]
    )]
    public function scheduleOrder(ScheduleOrderRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $order = $this->orderService->scheduleOrder(auth()->user(), $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Ordem agendada com sucesso',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ordem de carregamento não encontrada.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            Log::error("Erro ao agendar ordem: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao processar agendamento.'], 500);
        }
    }

    #[OA\Post(
        path: "/api/order/{orderId}/start-order",
        summary: "Inicia o processo de uma ordem de carregamento",
        security: [["bearerAuth" => []]],
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(
                name: "orderId",
                description: "UUID da ordem a ser iniciada",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Ordem iniciada com sucesso"),
            new OA\Response(response: 400, description: "A ordem não está no status 'scheduled'"),
            new OA\Response(response: 403, description: "Você não tem permissão para iniciar esta ordem"),
            new OA\Response(response: 404, description: "Ordem de carregamento não encontrada"),
            new OA\Response(response: 500, description: "Erro interno no servidor")
        ]
    )]
    public function startOrder($orderId): \Illuminate\Http\JsonResponse
    {
        try {
            $order = $this->orderService->startOrder(auth()->user(), $orderId);

            return response()->json([
                'success' => true,
                'message' => 'Ordem iniciada com sucesso',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ordem de carregamento não encontrada.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (BadRequestException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error("Erro ao iniciar ordem: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao processar início da ordem.'], 500);
        }
    }

    #[OA\Post(
        path: "/api/order/{orderId}/finish-order",
        summary: "Finaliza uma ordem de carregamento",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "justification", description: "Justificativa ou observação", type: "string")
                ]
            )
        ),
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(
                name: "orderId",
                description: "UUID da ordem a ser finalizada",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Ordem finalizada com sucesso"),
            new OA\Response(response: 400, description: "A ordem não está em andamento ou falta justificativa"),
            new OA\Response(response: 403, description: "Você não tem permissão para finalizar esta ordem"),
            new OA\Response(response: 404, description: "Ordem de carregamento não encontrada"),
            new OA\Response(response: 500, description: "Erro interno no servidor")
        ]
    )]
    public function finishOrder(Request $request, $orderId): \Illuminate\Http\JsonResponse    {
        try {
            $justification = $request->input('justification');

            $order = $this->orderService->finishOrder(auth()->user(), $orderId, $justification);

            return response()->json([
                'success' => true,
                'message' => 'Ordem finalizada com sucesso',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ordem de carregamento não encontrada.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (BadRequestException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error("Erro ao finalizar ordem: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao processar finalização da ordem.'], 500);
        }
    }
}
