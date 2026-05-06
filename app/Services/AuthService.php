<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @throws \Exception
     */
    public function loginService($request): string
    {
            $credentials = $request->only('email', 'password');

            $data = User::query()->where(['email' => $credentials['email']])->first();
            if (!$data || !Hash::check($credentials['password'], $data->password)) {
                throw new \Exception('Invalid credentials');
            }
        return $data->createToken('auth_token')->plainTextToken;
    }

    public function logoutService($user): void
    {
        $user->currentAccessToken()->delete();
    }
}
