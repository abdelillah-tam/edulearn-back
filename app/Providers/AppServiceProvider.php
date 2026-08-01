<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cache;
use Http;
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

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
        //
        Storage::extend('dropbox', function ($app, $config) {

            $accessToken = Cache::remember('dropbox_access_token', 14000, function () use ($config) {
                $response = Http::asForm()->post('https://api.dropbox.com/oauth2/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $config['refresh_token'],
                    'client_id' => $config['key'],
                    'client_secret' => $config['secret'],
                ]);

                if ($response->successful()) {
                    return $response->json()['access_token'];
                } else {
                    throw new \Exception('Failed to refresh Dropbox access token: ' . $response->body());
                }
            });

            $adapter = new DropboxAdapter(new Client(
                $accessToken
            ));

            return new FilesystemAdapter(
                new Filesystem(
                    $adapter,
                    $config
                ),
                $adapter,
                $config
            );
        });
    }
}
