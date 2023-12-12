<?php

namespace Silo\StorageConnectors\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;
use Silo\StorageConnectors\DTO\SiloFile;

/**
 * @method static SiloFile get(string $confluencePageId, bool $includeFileContent = false)
 * @method static LazyCollection<SiloFile> list(bool $includeFileContent = false, array $extraArgs = [], ?string $spaceId = null)
 *
 * @see \Silo\StorageConnectors\Connectors\ConfluenceConnector
 */
class ConfluenceSilo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'confluence_silo';
    }
}
