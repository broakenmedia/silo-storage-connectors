<?php

namespace Silo\StorageConnectors\Contracts;

use Illuminate\Support\Enumerable;
use Silo\StorageConnectors\DTO\SiloFile;

interface StorageConnectorInterface
{
    public function get(string $resourceId, bool $includeFileContent = false): SiloFile;

    /**
     * @return Enumerable<SiloFile>
     */
    public function list(array $extraArgs = [], bool $includeFileContent = false): Enumerable;
}
