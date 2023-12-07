<?php

namespace Silo\StorageConnectors\Commands;

use Illuminate\Console\Command;

class StorageConnectorsCommand extends Command
{
    public $signature = 'silo-storage-connectors';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
