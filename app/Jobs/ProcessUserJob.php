<?php

namespace App\Jobs;

use App\DTOs\Integration\UserIntegrationDTO;
use App\Exceptions\IntegrationException;
use App\Services\IntegrationLogService;
use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de tentativas antes de considerar o job como falho.
     */
    public int $tries = 5;

    /**
     * Tempo (em segundos) entre as tentativas: 10s, 30s, 1min, 5min.
     */
    public array $backoff = [10, 30, 60, 300];

    /**
     * Tempo máximo (em segundos) para execução do job.
     */
    public int $timeout = 60;

    public function __construct(
        private readonly array $payload
    ) {}

    /**
     * @throws \Throwable
     * @throws IntegrationException
     */
    public function handle(UserService $service): void
    {
        try {

            $dto = UserIntegrationDTO::fromArray($this->payload);
            $service->storeUser($dto);

        } catch (IntegrationException $e) {

            /**
             * Erros de negócio (4xx) não devem ser reprocessados.
             */
            if ($e->getHttpStatus() >= 400 && $e->getHttpStatus() < 500) {
                $this->fail($e);
                return;
            }

            /**
             * Erros 5xx podem ser temporários → retry automático.
             */
            throw $e;

        } catch (\Throwable $e) {

            /**
             * Erro inesperado → retry automático.
             */
            throw $e;
        }
    }

    /**
     * Callback executado quando o job esgota todas as tentativas.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to process user integration', [
            'payload' => $this->payload,
            'error'   => $exception->getMessage(),
            'trace'   => $exception->getTraceAsString(),
        ]);

        try {

            app(IntegrationLogService::class)->log(
                '/api/integration/user',
                $this->payload,
                500,
                $exception->getMessage()
            );

        } catch (\Throwable $e) {

            Log::error('Failed to log user integration error', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
