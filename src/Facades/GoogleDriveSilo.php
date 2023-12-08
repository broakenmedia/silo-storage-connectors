<?php

namespace Silo\StorageConnectors\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Silo\StorageConnectors\DTO\StorageResponse;

/**
 * @method static StorageResponse get(string $resourceId, bool $includeFileContent = false)
 * @method static Collection list(?string $pageId = null, int $pageSize = 20, bool $includeFileContent = false, array $extraArgs = [])
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
