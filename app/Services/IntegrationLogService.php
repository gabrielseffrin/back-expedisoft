<?php

namespace App\Services;

use App\Models\IntegrationLog;

class IntegrationLogService
{
    public function log(string $endpoint, array $payload, int $httpStatus, ?string $errorMessage = null): void
    {
        IntegrationLog::query()->create([
            'endpoint' => $endpoint,
            'payload' => $payload,
            'http_status' => $httpStatus,
            'error_message' => $errorMessage,
        ]);
    }
}
