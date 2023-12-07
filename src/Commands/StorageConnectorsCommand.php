<?php

namespace Silo\StorageConnectors\Commands;

use Illuminate\Console\Command;
use Silo\StorageConnectors\Factories\StorageConnectorFactory;

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
