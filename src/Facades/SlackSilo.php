<?php

namespace Silo\StorageConnectors\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;
use Saloon\Http\Faking\MockClient;
use Silo\StorageConnectors\DTO\SiloFile;

/**
 * @method static SiloFile get(string $fileId, bool $includeFileContent = false)
 * @method static LazyCollection<SiloFile> list(array $extraArgs = [], bool $includeFileContent = false, ?string $channelId = null)
 * @method static void setMockClient(MockClient $mockClient)
 *
 * @see \Silo\StorageConnectors\Connectors\SlackConnector
 */
class SlackSilo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'slack_silo';
    }
}
