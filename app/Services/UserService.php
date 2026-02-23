<?php

namespace App\Services;

use AllowDynamicProperties;
use App\Enums\HttpStatus;
use App\Exceptions\IntegrationException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[AllowDynamicProperties] class UserService
{

    private const ENDPOINT = '/api/integration/user';

    public function __construct(
        private readonly EntityService         $entityService,
        private readonly IntegrationLogService $logService
    )
    {
    }

    /**
     * @param array $data
     * @return User
     * @throws IntegrationException
     */
    public function storeUser(array $data): User
    {
        DB::beginTransaction();
        try {
            $this->validateData($data);

            $user = $this->processUser($data);

            DB::commit();

            $this->logService->log(
                self::ENDPOINT,
                $data,
                HttpStatus::OK->value,
                null
            );

            return $user;

        } catch (IntegrationException $e) {
            DB::rollBack();
            $this->logError($data, $e->getHttpStatus(), $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro inesperado na integração de usuário', [
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
        if (empty($data['user'])) {
            throw new IntegrationException(
                'Dados do usuário não informados',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }

        if (empty($data['source_system'])) {
            throw new IntegrationException(
                'Sistema de origem não informado',
                HttpStatus::UNPROCESSABLE_ENTITY->value
            );
        }
    }

    private function processUser(array $data): User
    {
        $userData = $data['user'];
        $sourceSystem = $data['source_system'];

        return $this->entityService->findOrCreateUser([
            'external_id' => $userData['external_id'] ?? null,
            'source_system' => $sourceSystem,
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    private function logError(array $data, int $httpStatus, string $message): void
    {
        $this->logService->log(
            self::ENDPOINT,
            $data,
            $httpStatus,
            $message
        );
    }
}
