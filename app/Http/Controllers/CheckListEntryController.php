<?php

namespace App\Http\Controllers;

use App\Models\ChecklistEntry;
use App\Models\LoadingOrder;
use Illuminate\Http\Request;

class CheckListEntryController extends Controller
{
    public function store(Request $request, $orderId): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $authUser = auth()->user();

        $order = LoadingOrder::query()
            ->where('id', $orderId)
            ->where('operator_id', $authUser->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Ordem de carregamento não encontrada.'], 404);
        }

        if ($order->status !== 'in_progress') {
            return response()->json(['message' => 'A ordem precisa estar em andamento para carregar itens.'], 400);
        }

        $package = $order->items()
            ->with('packages')
            ->get()
            ->pluck('packages')
            ->flatten()
            ->firstWhere('unique_package_code', $request->input('qr_code'));

        if (!$package) {
            return response()->json(['message' => 'Código QR não corresponde a nenhum pacote da ordem de carregamento.'], 400);
        }

        $is_checked = ChecklistEntry::query()
            ->where('loading_order_id', $order->id)
            ->where('package_id', $package->id)
            ->exists();

        if ($is_checked) {
            return response()->json(['message' => 'Este pacote já foi conferido.'], 400);
        }

        ChecklistEntry::query()->create([
            'loading_order_id' => $order->id,
            'package_id' => $package->id,
            'scanned_at' => now(),
            'scanned_by' => $request->user()->id,
            'scanned_code' => $request->input('qr_code'),
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Pacote conferido com sucesso',
        ], 200);
    }
}
