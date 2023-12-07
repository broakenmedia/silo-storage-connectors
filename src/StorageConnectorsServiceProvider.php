<?php

namespace Silo\StorageConnectors;

use Silo\StorageConnectors\Commands\StorageConnectorsCommand;
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
    }
}
