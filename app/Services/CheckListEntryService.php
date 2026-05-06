<?php

namespace App\Services;

use App\Models\ChecklistEntry;
use App\Models\LoadingOrder;
use App\Models\ScanLogs;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use App\Models\User;

class CheckListEntryService
{
    public function store(User $operator, $orderId, string $scannedCode)
    {
        $query = LoadingOrder::query()->where('id', $orderId);
        
        if ($operator->rule !== 'admin') {
            $query->where('operator_id', $operator->id);
        }

        $order = $query->first();

        if (!$order) {
            $this->logScan($orderId, $operator->id, $scannedCode, null, 'error', 'Ordem de carregamento não encontrada.');
            throw new ModelNotFoundException('Ordem de carregamento não encontrada.');
        }

        if ($order->status !== 'in_progress') {
            $this->logScan($order->id, $operator->id, $scannedCode, null, 'error', 'A ordem precisa estar em andamento para carregar itens.');
            throw new BadRequestException('A ordem precisa estar em andamento para carregar itens.');
        }

        $package = $order->items()
            ->with(['packages' => function ($query) use ($scannedCode) {
                $query->where('unique_package_code', $scannedCode);
            }])
            ->get()
            ->pluck('packages')
            ->flatten()
            ->first();

        if (!$package) {
            $this->logScan($order->id, $operator->id, $scannedCode, null, 'error', 'Código QR não corresponde a nenhum pacote da ordem de carregamento.');
            throw new BadRequestException('Código QR não corresponde a nenhum pacote da ordem de carregamento.');
        }

        $isChecked = ChecklistEntry::query()
            ->where('loading_order_id', $order->id)
            ->where('package_id', $package->id)
            ->exists();

        if ($isChecked) {
            $this->logScan($order->id, $operator->id, $scannedCode, $package->id, 'error', 'Este pacote já foi conferido.');
            throw new BadRequestException('Este pacote já foi conferido.');
        }

        return DB::transaction(function () use ($order, $operator, $scannedCode, $package) {
            $this->logScan($order->id, $operator->id, $scannedCode, $package->id, 'success');

            return ChecklistEntry::query()->create([
                'loading_order_id' => $order->id,
                'package_id'       => $package->id,
                'scanned_at'       => now(),
                'scanned_by'       => $operator->id,
                'scanned_code'     => $scannedCode,
            ]);
        });
    }

    private function logScan($orderId, $operatorId, $scannedCode, $packageId = null, string $status = 'success', string $errorMessage = null): void
    {
        ScanLogs::query()->create([
            'loading_order_id' => $orderId,
            'operator_id'      => $operatorId,
            'package_id'       => $packageId,
            'scanned_code'     => $scannedCode,
            'status'           => $status,
            'error_message'    => $errorMessage,
            'scanned_at'       => now(),
            'payload'          => json_encode([
                'loading_order_id' => $orderId,
                'package_id'       => $packageId,
                'scanned_by'       => $operatorId,
                'scanned_code'     => $scannedCode,
            ]),
        ]);
    }
}
