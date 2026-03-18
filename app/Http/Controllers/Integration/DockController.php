<?php

namespace App\Http\Controllers\Integration;

use App\Exceptions\IntegrationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\DockIntegrationRequest;
use App\Jobs\ProcessDockJob;

class DockController extends Controller
{
    public function storeDock(DockIntegrationRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            ProcessDockJob::dispatch($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payload received and queued for processing'
            ], 202);

        } catch (IntegrationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
