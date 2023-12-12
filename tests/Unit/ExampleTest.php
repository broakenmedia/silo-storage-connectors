<?php

namespace Silo\StorageConnectors\Tests\Unit;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Psr7;
use Mockery;
use Silo\StorageConnectors\DTO\SiloFile;
use Silo\StorageConnectors\Facades\ConfluenceSilo;
use Silo\StorageConnectors\Facades\GoogleDriveSilo;

it('can get single file from Google Drive', function () {
    $mockDriveFile = new DriveFile([
        'id' => 'FILEID',
        'kind' => 'drive#file',
        'mimeType' => 'video/mp4',
        'fileExtension' => 'mp4',
        'size' => '123456',
        'videoMediaMetadataType' => "Google\Service\Drive\DriveFileVideoMediaMetadata",
        'webViewLink' => 'https://drive.google.com/file/d/FILEID/view?usp=drivesdk',
        'name' => 'testvideofile.txt',
    ]);

    $driveFilesServiceMock = Mockery::mock(Drive\Resource\Files::class);
    $driveFilesServiceMock->shouldReceive('get')->withArgs(['FILEID', ['fields' => 'id,kind,name,mimeType,size,exportLinks,fileExtension,webViewLink']])
        ->andReturn($mockDriveFile);

    $driveFilesServiceMock->shouldReceive('get')->withArgs(['FILEID', ['alt' => 'media']])
        ->andReturn(new Psr7\Response());

    /** @var Drive $driveServiceMock */
    $driveServiceMock = Mockery::mock(Drive::class)->makePartial();
    $driveServiceMock->__construct(app(Client::class));
    $driveServiceMock->files = $driveFilesServiceMock;

    /** @phpstan-ignore-next-line */
    $this->app->instance(Drive::class, $driveServiceMock);

    $file = GoogleDriveSilo::get('FILEID', true);
    expect($file)->toBeInstanceOf(SiloFile::class)->
    and($file->id)->toBe('FILEID');
});

it('can get file list from Google Drive', function () {

});

it('can access confluence', function () {
    $spaceId = '12345';
    //$conf = StorageConnectorFactory::connect(SiloConnector::CONFLUENCE);
    $file = ConfluenceSilo::get($spaceId, true);
    expect($file->id)->toBe($spaceId);

    $files = ConfluenceSilo::list(false, [], 59179012);
    expect($files->first()->id)->toBe($spaceId)
        ->and($files)->not()->toBeEmpty();
});
