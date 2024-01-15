<?php

namespace Silo\StorageConnectors\Factories;

use Silo\StorageConnectors\Connectors\ConfluenceConnector;
use Silo\StorageConnectors\Connectors\GoogleDriveConnector;
use Silo\StorageConnectors\Connectors\SlackConnector;
use Silo\StorageConnectors\Contracts\StorageConnectorInterface;
use Silo\StorageConnectors\Enums\SiloConnector;
use Silo\StorageConnectors\Exceptions\StorageException;

class StorageConnectorFactory
{
    public static array $customConnectors = [];

    public static function registerConnector($connectorName, $connectorClass)
    {
        self::$customConnectors[$connectorName] = $connectorClass;
    }

    /**
     * @throws StorageException
     */
    public static function connect(SiloConnector|string $connectorType): StorageConnectorInterface
    {
        if (is_string($connectorType) && isset(self::$customConnectors[$connectorType])) {
            return new self::$customConnectors[$connectorType]();
        }

        // Logic for built-in providers
        return match ($connectorType) {
            SiloConnector::GOOGLE_DRIVE => new GoogleDriveConnector(),
            SiloConnector::CONFLUENCE => new ConfluenceConnector(),
            SiloConnector::SLACK => new SlackConnector(),
            default => throw new StorageException("Unsupported storage provider: $connectorType"),
        };
    }
}
