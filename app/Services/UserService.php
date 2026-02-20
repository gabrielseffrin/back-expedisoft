<?php

namespace App\Services;

use AllowDynamicProperties;
use App\Models\User;
use Illuminate\Support\Facades\DB;

#[AllowDynamicProperties] class UserService
{

    public function __construct(EntityService $entityService)
    {
        $this->entityService = $entityService;
    }

    /**
     * @throws \Exception
     */
    public function storeOrder(array $data): User
    {
        DB::beginTransaction();
        try {
            $userData = $data['user'];
            $souceSystem = $data['source_system'];

            $user = $this->entityService->findOrCreateUser(
                [
                    'external_id' => $userData['external_id'] ?? null,
                    'source_system' => $souceSystem['source_system'] ?? null,
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                ]
            );
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
