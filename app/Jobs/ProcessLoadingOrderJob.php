<?php

namespace App\Jobs;

use App\Exceptions\IntegrationException;
use App\Services\IntegrationLogService;
use App\Services\LoadingOrderIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessLoadingOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public $backoff = [10, 30, 60, 300];

    public $timeout = 120;

    public function __construct(
        private readonly array $payload
    ) {}

    /**
     * @throws \Throwable
     * @throws IntegrationException
     */
    public function handle(LoadingOrderIntegrationService $service): void
    {
        try {

            $service->storeOrder($this->payload);

        } catch (IntegrationException $e) {

            /**
             * Erros de negócio (4xx) não devem ser reprocessados
             */
            if ($e->getHttpStatus() >= 400 && $e->getHttpStatus() < 500) {
                $this->fail($e);
                return;
            }

            /**
             * Erros 5xx podem ser temporários → retry
             */
            throw $e;

        } catch (\Throwable $e) {

            /**
             * Erro inesperado → retry automático
             */
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to process loading order integration', [
            'payload' => $this->payload,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        try {

            app(IntegrationLogService::class)->log(
                '/api/integration/order',
                $this->payload,
                500,
                $exception->getMessage()
            );

        } catch (\Throwable $e) {

            Log::error('Failed to log integration error', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
