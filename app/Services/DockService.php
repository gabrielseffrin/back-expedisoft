<?php

namespace App\Services;

use App\DTOs\Entity\DockDTO;
use App\DTOs\Integration\DockIntegrationDTO;
use App\Enums\HttpStatus;
use App\Exceptions\IntegrationException;
use App\Models\Dock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DockService
{
    const ENDPOINT = '/api/integration/dock';

    public function __construct(
        private readonly EntityService         $entityService,
        private readonly IntegrationLogService $logService
    ) {
    }

    /**
     * @param DockIntegrationDTO $dto
     * @return void
     * @throws IntegrationException
     */
    public function storeDock(DockIntegrationDTO $dto): void
    {
        DB::beginTransaction();
        try {
            $this->entityService->findOrCreateDock($dto->dock);

            DB::commit();

            $this->logService->log(
                self::ENDPOINT,
                ['source_system' => $dto->sourceSystem, 'dock_code' => $dto->dock->dockCode],
                HttpStatus::OK->value,
                null
            );

        } catch (IntegrationException $e) {
            DB::rollBack();
            $this->logError(['source_system' => $dto->sourceSystem], $e->getHttpStatus(), $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro inesperado na integração de dock', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->logError(['source_system' => $dto->sourceSystem], HttpStatus::INTERNAL_SERVER_ERROR->value, $e->getMessage());
            throw new IntegrationException(
                'Erro interno ao processar integração',
                HttpStatus::INTERNAL_SERVER_ERROR->value
            );
        }
    }

    public function getAllDocks(): \Illuminate\Database\Eloquent\Collection
    {
        return Dock::all();
    }

    private function logError(array $data, int $status, string $message): void
    {
        $this->logService->log(
            self::ENDPOINT,
            $data,
            $status,
            $message
        );
    }
}
