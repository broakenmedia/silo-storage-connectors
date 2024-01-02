<?php

namespace Silo\StorageConnectors\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;
use Silo\StorageConnectors\DTO\SiloFile;

/**
 * @method static SiloFile get(string $fileId, bool $includeFileContent = false)
 * @method static LazyCollection<SiloFile> list(array $extraArgs = [], bool $includeFileContent = false)
 *
 * @see \Silo\StorageConnectors\Connectors\GoogleDriveConnector
 */
class GoogleDriveSilo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'google_drive_silo';
    }
}
