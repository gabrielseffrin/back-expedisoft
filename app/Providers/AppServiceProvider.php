<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        /**
         * Rate limiter para endpoints de integração com ERP.
         * Conta por API Key (não por IP)
         * independente por cliente integrado.
         * Configurável via config('services.integration.rate_limit').
         */
        RateLimiter::for('integration', function (Request $request) {
            $apiKey   = $request->header('X-API-KEY', 'unknown');
            $maxCalls = config('services.integration.rate_limit', 60);

            return Limit::perMinute($maxCalls)
                ->by($apiKey)
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests. Please slow down.',
                    ], 429);
                });
        });
    }
}
