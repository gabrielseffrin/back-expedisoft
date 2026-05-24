<?php

namespace App\Services;

use AllowDynamicProperties;
use App\DTOs\Integration\UserIntegrationDTO;
use App\DTOs\Entity\UserDTO;
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
    ) {
    }

    /**
     * @param UserIntegrationDTO $dto
     * @return User
     * @throws IntegrationException
     */
    public function storeUser(UserIntegrationDTO $dto): User
    {
        DB::beginTransaction();
        try {
            $user = $this->entityService->findOrCreateUser($dto->user);

            DB::commit();

            $this->logService->log(
                self::ENDPOINT,
                ['source_system' => $dto->sourceSystem, 'email' => $dto->user->email],
                HttpStatus::OK->value,
                null
            );

            return $user;

        } catch (IntegrationException $e) {
            DB::rollBack();
            $this->logError(['source_system' => $dto->sourceSystem], $e->getHttpStatus(), $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro inesperado na integração de usuário', [
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

    private function logError(array $data, int $httpStatus, string $message): void
    {
        $this->logService->log(
            self::ENDPOINT,
            $data,
            $httpStatus,
            $message
        );
    }

    public function getOperators(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()->where('rule', 'operador')->get(['id', 'name', 'email']);
    }
}
