<?php

namespace Silo\StorageConnectors\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Silo\StorageConnectors\StorageConnectors
 */
class StorageConnectors extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Silo\StorageConnectors\StorageConnectors::class;
    }
}
