<?php

namespace Silo\StorageConnectors\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Silo\StorageConnectors\DTO\SiloFile;

/**
 * @method static SiloFile get(string $fileId, bool $includeFileContent = false)
 * @method static Collection<SiloFile> list(bool $includeFileContent = false, array $extraArgs = [])
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
