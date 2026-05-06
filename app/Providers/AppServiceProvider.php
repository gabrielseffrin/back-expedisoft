<?php

namespace App\Providers;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

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
        $this->registerGoogleDriveDriver();
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

    private function registerGoogleDriveDriver(): void
    {
        try {
            Storage::extend('google', function ($app, $config) {
                $options = [];

                $sharedFolderId = $config['sharedFolderId'] ?? null;
                if (empty($sharedFolderId) && !empty($config['folderId'] ?? null)) {
                    $sharedFolderId = $config['folderId'];
                }

                if (!empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                if (!empty($sharedFolderId)) {
                    $options['sharedFolderId'] = $sharedFolderId;
                }

                $client = new GoogleClient();
                $client->setClientId($config['clientId'] ?? null);
                $client->setClientSecret($config['clientSecret'] ?? null);
                $client->setAccessType('offline');
                $client->setScopes([GoogleDrive::DRIVE]);

                $refreshToken = $config['refreshToken'] ?? null;
                $client->refreshToken($refreshToken);
                $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);

                if (isset($token['error'])) {
                    Log::error('Falha ao obter access token do Google Drive.', [
                        'error' => $token['error'],
                        'error_description' => $token['error_description'] ?? null,
                    ]);

                    throw new \RuntimeException('Falha ao autenticar no Google Drive.');
                }

                $service = new GoogleDrive($client);
                $root = $config['folder'] ?? '/';
                $adapter = new GoogleDriveAdapter($service, $root, $options);
                $driver = new Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Throwable $e) {
            Log::error('Falha ao registrar o driver do Google Drive.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
