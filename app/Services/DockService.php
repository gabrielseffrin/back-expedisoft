<?php

namespace App\Services;

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
    )
    {
    }

    /**
     * @param array $data
     * @return void
     * @throws IntegrationException
     */
    public function storeDock(array $data): void
    {
        DB::beginTransaction();
        try {
            $this->validateData($data);

            $this->processDock($data);

            DB::commit();

            $this->logService->log(
                self::ENDPOINT,
                $data,
                HttpStatus::OK->value,
                null
            );

        } catch (IntegrationException $e) {
            DB::rollBack();
            $this->logError($data, $e->getHttpStatus(), $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro inesperado na integração de dock', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->logError($data, HttpStatus::INTERNAL_SERVER_ERROR->value, $e->getMessage());
            throw new IntegrationException(
                'Erro interno ao processar integração',
                HttpStatus::INTERNAL_SERVER_ERROR->value
            );
        }
    }

    /**
     * @throws IntegrationException
     */
    private function validateData(array $data): void
    {
        if (empty($data['dock']['external_id'])) {
            throw new IntegrationException(
                'Dados de dock inválidos: external_id é obrigatório',
                HttpStatus::BAD_REQUEST->value
            );
        }

        if (empty($data['dock']['dock_code'])) {
            throw new IntegrationException(
                'Dados de dock inválidos: dock_code é obrigatório',
                HttpStatus::BAD_REQUEST->value
            );
        }

        if (empty($data['source_system'])) {
            throw new IntegrationException(
                'Dados de dock inválidos: source_system é obrigatório',
                HttpStatus::BAD_REQUEST->value
            );
        }
    }

    private function processDock(array $data): void
    {
        $dock = $data['dock'];
        $sourceSystem = $data['source_system'];

        $this->entityService->findOrCreateDock([
                'external_id' => $dock['external_id'],
                'source_system' => $sourceSystem,
                'dock_code' => $dock['dock_code'],
                'description' => $dock['description'] ?? null,
                'location' => $dock['location'] ?? null]
        );
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
