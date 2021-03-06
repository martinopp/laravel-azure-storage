<?php

namespace Matthewbdaly\LaravelAzureStorage;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Service provider for Azure Blob Storage
 */
final class AzureStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('azure', function ($app, $config) {
            $client = $app->make(BlobRestProxy::class, $config);
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container'],
                $config['key'],
                $config['url'] ?? null,
                $config['prefix'] ?? null
            );
            return new Filesystem($adapter, $config);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BlobRestProxy::class, function ($app, $config) {
            $config = empty($config) ? $app->make('config')->get('filesystems.disks.azure') : $config;
            $endpoint = sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;',
                $config['name'],
                $config['key']
            );
            if (isset($config['endpoint'])) {
                $endpoint .= sprintf("BlobEndpoint=%s;", $config['endpoint']);
            }
            return BlobRestProxy::createBlobService($endpoint);
        });
    }
}
