<?php

namespace Silo\StorageConnectors;

use Google\Client;
use Google\Service\Drive;
use Silo\StorageConnectors\Commands\StorageConnectorsCommand;
use Silo\StorageConnectors\Enums\SiloConnector;
use Silo\StorageConnectors\Factories\StorageConnectorFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class StorageConnectorsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('silo-storage-connectors')
            ->hasConfigFile('silo')
            ->hasViews()
            ->hasMigration('create_silo-storage-connectors_table')
            ->hasCommand(StorageConnectorsCommand::class);

        $this->app->singleton(StorageConnectorFactory::class, function () {
            return new StorageConnectorFactory();
        });

        $this->app->bind(Client::class, function () {
            $client = new Client();
            $client->setAuthConfig(config('silo.google_drive.service_account'));
            $client->setScopes(Drive::DRIVE);

            return $client;
        });

        $this->app->bind(Drive::class, function () {
            return new Drive(app(Client::class));
        });

        $this->app->singleton('google_drive_silo', function () {
            return StorageConnectorFactory::connect(SiloConnector::GOOGLE_DRIVE);
        });

        $this->app->singleton('confluence_silo', function () {
            return StorageConnectorFactory::connect(SiloConnector::CONFLUENCE);
        });

    }
}
