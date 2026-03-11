<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $db = $this->checkDatabase();

        $status = $db === 'ok' ? 'ok' : 'degraded';
        $httpCode = $db === 'ok' ? 200 : 503;

        return response()->json([
            'status'    => $status,
            'services'  => [
                'database' => $db,
                'queue'    => $this->checkQueue(),
            ],
            'timestamp' => now()->toIso8601String(),
        ], $httpCode);
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }

    private function checkQueue(): string
    {
        try {
            return Queue::size() >= 0 ? 'ok' : 'error';
        } catch (\Throwable) {
            return 'error';
        }
    }
}
