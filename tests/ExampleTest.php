<?php

use Silo\StorageConnectors\Factories\StorageConnectorFactory;

it('can test', function () {
    $gDrive = StorageConnectorFactory::create('google_drive');

    $file = $gDrive->get('FILE ID');
    expect($file->id)->not()->toBeNull();

    $files = $gDrive->list(null, 5);
    expect($files)->not()->toBeEmpty();
});
