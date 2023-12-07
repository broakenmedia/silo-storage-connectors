<?php

namespace Silo\StorageConnectors\Factories;

use Silo\StorageConnectors\Connectors\GoogleDriveConnector;
use Silo\StorageConnectors\Contracts\StorageConnectorInterface;
use Silo\StorageConnectors\Exceptions\StorageException;

class StorageConnectorFactory
{
    protected static array $customProviders = [];

    public static function registerProvider($providerName, $providerClass)
    {
        self::$customProviders[$providerName] = $providerClass;
    }

    /**
     * @throws StorageException
     */
    public static function create(string $providerType): StorageConnectorInterface
    {
        if (isset(self::$customProviders[$providerType])) {
            return new self::$customProviders[$providerType]();
        }

        // Logic for built-in providers
        return match ($providerType) {
            'google_drive' => new GoogleDriveConnector(config('storageproviders.google_drive')),
            default => throw new StorageException("Unsupported storage provider: {$providerType}"),
        };
    }
}
