<?php

namespace Silo\StorageConnectors\Contracts;

use Illuminate\Support\Collection;
use Silo\StorageConnectors\DTO\StorageResponse;

interface StorageConnectorInterface
{
    public function get(string $fileId, bool $includeFileContent = false): StorageResponse;

    public function list(?string $pageId = null, int $pageSize = 20, bool $includeFileContent = false): Collection;
}
