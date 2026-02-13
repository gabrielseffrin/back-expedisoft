<?php

namespace App\Traits;

trait HttpResponses
{
    protected function success(mixed $data, $message = null, int $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error(string $message, int $code, mixed $errors = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'data' => null
        ], $code);
    }
}
