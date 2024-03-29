<?php

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\FileList;
use GuzzleHttp\Psr7;
use Illuminate\Support\Facades\Config;
use Saloon\Config as SaloonConfig;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Silo\StorageConnectors\DTO\SiloFile;
use Silo\StorageConnectors\Facades\ConfluenceSilo;
use Silo\StorageConnectors\Facades\GoogleDriveSilo;
use Silo\StorageConnectors\Facades\SlackSilo;

beforeEach(function () {
    SaloonConfig::preventStrayRequests();
});

it('can get single file without file content from Google Drive', function () {

    Config::set('silo.google_drive.service_account', 'mock-service-account.json');

    $mockFileID = 'FILEID';

    $mockDriveFile = new DriveFile([
        'id' => $mockFileID,
        'kind' => 'drive#file',
        'mimeType' => 'video/mp4',
        'fileExtension' => 'mp4',
        'size' => '123456',
        'videoMediaMetadataType' => "Google\Service\Drive\DriveFileVideoMediaMetadata",
        'webViewLink' => "https://drive.google.com/file/d/$mockFileID/view?usp=drivesdk",
        'name' => 'testvideofile.txt',
    ]);

    $driveFilesServiceMock = Mockery::mock(Drive\Resource\Files::class);
    $driveFilesServiceMock->shouldReceive('get')->withArgs([$mockFileID, ['fields' => 'id,kind,name,mimeType,size,exportLinks,fileExtension,webViewLink']])
        ->andReturn($mockDriveFile);

    $driveFilesServiceMock->shouldReceive('get')->withArgs([$mockFileID, ['alt' => 'media']])
        ->andReturn(new Psr7\Response());

    /** @var Drive $driveServiceMock */
    $driveServiceMock = Mockery::mock(Drive::class)->makePartial();
    $driveServiceMock->__construct(app(Client::class));
    $driveServiceMock->files = $driveFilesServiceMock;

    /** @phpstan-ignore-next-line */
    $this->app->instance(Drive::class, $driveServiceMock);

    $file = GoogleDriveSilo::get($mockFileID);
    expect($file)->toBeInstanceOf(SiloFile::class)->
    and($file->id)->toBe($mockFileID)->and($file->contentStream())->toBeNull();
});

it('can get single file with file content from Google Drive', function () {

    Config::set('silo.google_drive.service_account', 'mock-service-account.json');

    $mockFileID = 'FILEID';

    $mockDriveFile = new DriveFile([
        'id' => $mockFileID,
        'kind' => 'drive#file',
        'mimeType' => 'video/mp4',
        'fileExtension' => 'mp4',
        'size' => '123456',
        'videoMediaMetadataType' => "Google\Service\Drive\DriveFileVideoMediaMetadata",
        'webViewLink' => "https://drive.google.com/file/d/$mockFileID/view?usp=drivesdk",
        'name' => 'testvideofile.txt',
    ]);

    $driveFilesServiceMock = Mockery::mock(Drive\Resource\Files::class);
    $driveFilesServiceMock->shouldReceive('get')->withArgs([$mockFileID, ['fields' => 'id,kind,name,mimeType,size,exportLinks,fileExtension,webViewLink']])
        ->andReturn($mockDriveFile);

    $driveFilesServiceMock->shouldReceive('get')->withArgs([$mockFileID, ['alt' => 'media']])
        ->andReturn(new Psr7\Response());

    /** @var Drive $driveServiceMock */
    $driveServiceMock = Mockery::mock(Drive::class)->makePartial();
    $driveServiceMock->__construct(app(Client::class));
    $driveServiceMock->files = $driveFilesServiceMock;

    /** @phpstan-ignore-next-line */
    $this->app->instance(Drive::class, $driveServiceMock);

    $file = GoogleDriveSilo::get($mockFileID, true);
    expect($file)->toBeInstanceOf(SiloFile::class)
        ->and($file->id)->toBe($mockFileID)
        ->and($file->contentStream())->not->toBeNull();
});

it('can get file list without file content from Google Drive', function () {

    Config::set('silo.google_drive.service_account', 'mock-service-account.json');

    $mockFileID = 'FILEID';

    $mockDriveFile = new DriveFile([
        'id' => $mockFileID,
        'kind' => 'drive#file',
        'mimeType' => 'video/mp4',
        'fileExtension' => 'mp4',
        'size' => '123456',
        'videoMediaMetadataType' => "Google\Service\Drive\DriveFileVideoMediaMetadata",
        'webViewLink' => "https://drive.google.com/file/d/$mockFileID/view?usp=drivesdk",
        'name' => 'testvideofile.txt',
    ]);

    /** @var FileList $driveFileListMock */
    $driveFileListMock = Mockery::mock(FileList::class)->makePartial();
    $driveFileListMock->setFiles([$mockDriveFile]);

    $driveFilesServiceMock = Mockery::mock(Drive\Resource\Files::class);
    $driveFilesServiceMock->shouldReceive('listFiles')->andReturn($driveFileListMock);

    $driveFilesServiceMock->shouldReceive('get')->withArgs([$mockFileID, ['fields' => 'id,kind,name,mimeType,size,exportLinks,fileExtension,webViewLink']])
        ->andReturn($mockDriveFile);

    $driveFilesServiceMock->shouldReceive('get')->withArgs([$mockFileID, ['alt' => 'media']])
        ->andReturn(new Psr7\Response());

    /** @var Drive $driveServiceMock */
    $driveServiceMock = Mockery::mock(Drive::class)->makePartial();
    $driveServiceMock->__construct(app(Client::class));
    $driveServiceMock->files = $driveFilesServiceMock;

    /** @phpstan-ignore-next-line */
    $this->app->instance(Drive::class, $driveServiceMock);

    $rawnetProjectsFolder = '0B0V-HC-FCnqoNFpodlZxUmJZaEE';
    $files = GoogleDriveSilo::list(['q' => [
        'trashed' => false,
        "'$rawnetProjectsFolder' in parents",
    ], 'pageSize' => 5, 'pageToken' => null], false);

    /** @var SiloFile $file */
    $file = $files->first();

    expect($files)
        ->not()->toBeEmpty()
        ->and($file)->toBeInstanceOf(SiloFile::class)
        ->and($file->id)->toBe($mockFileID)
        ->and($file->contentStream())->toBeNull();
});

it('can get single file without content from confluence', function () {

    Config::set('silo.confluence.api_token', '');
    Config::set('silo.confluence.domain', '');
    Config::set('silo.confluence.username', '');

    $mockClient = new MockClient([
        MockResponse::make([
            'parentType' => 'page',
            'id' => '58949677',
            'title' => 'Hubspot: Lifecycles',
            'status' => 'current',
            'body' => [],
            'spaceId' => '59179012',
            '_links' => [
                'editui' => '',
                'webui' => '',
                'tinyui' => '',
            ],
        ]),
    ]);

    ConfluenceSilo::setMockClient($mockClient);

    $pageId = '58949677';

    $file = ConfluenceSilo::get($pageId);
    expect($file->id)->toBe($pageId)
        ->and($file)->toBeInstanceOf(SiloFile::class)
        ->and($file->contentStream())->toBeNull();
});

it('can get single file from confluence', function () {

    Config::set('silo.confluence.api_token', '');
    Config::set('silo.confluence.domain', '');
    Config::set('silo.confluence.username', '');

    $mockClient = new MockClient([
        MockResponse::make([
            'parentType' => 'page',
            'id' => '58949677',
            'title' => 'Hubspot: Lifecycles',
            'status' => 'current',
            'body' => [
                'storage' => [
                    'value' => 'This is the page content',
                    'representation' => 'storage',
                ],
            ],
            'spaceId' => '59179012',
            '_links' => [
                'editui' => '',
                'webui' => '',
                'tinyui' => '',
            ],
        ]),
    ]);

    ConfluenceSilo::setMockClient($mockClient);

    $pageId = '58949677';

    $file = ConfluenceSilo::get($pageId, true);
    expect($file->id)->toBe($pageId)
        ->and($file)->toBeInstanceOf(SiloFile::class)
        ->and($file->contentStream())->not()->toBeNull();
});

it('can get file list without file content from confluence', function () {

    Config::set('silo.confluence.api_token', '');
    Config::set('silo.confluence.domain', '');
    Config::set('silo.confluence.username', '');

    $mockClient = new MockClient([
        MockResponse::make([
            'results' => [[
                'id' => '58949677',
                'title' => 'Mock Page 1',
                'body' => [],
                'spaceId' => '59179012',
            ]],
            '_links' => [
                'next' => '/wiki/api/v2/spaces/59179012/pages?body-format=storage&cursor=cursorId',
            ],
        ]),
        MockResponse::make([
            'results' => [[
                'id' => '589496775',
                'title' => 'Mock Page 2',
                'body' => [
                    'storage' => [
                        'value' => 'This is the page content',
                        'representation' => 'storage',
                    ],
                ],
                'spaceId' => '59179012',
            ]],
            '_links' => [],
        ]),
    ]);

    ConfluenceSilo::setMockClient($mockClient);

    $pageId = '58949677';
    $spaceId = '59179012';

    $files = ConfluenceSilo::list([], false, $spaceId);
    $items = $files->all();
    expect($items[0]->id)->toBe($pageId)
        ->and($items[0]->contentStream())->toBeNull()
        ->and($items)->toHaveCount(2);
});

it('can get file list with file content from confluence', function () {

    Config::set('silo.confluence.api_token', '');
    Config::set('silo.confluence.domain', '');
    Config::set('silo.confluence.username', '');

    $mockClient = new MockClient([
        MockResponse::make([
            'results' => [[
                'id' => '58949677',
                'title' => 'Mock Page 1',
                'body' => [
                    'storage' => [
                        'value' => 'This is the page content',
                        'representation' => 'storage',
                    ],
                ],
                'spaceId' => '59179012',
            ]],
            '_links' => [
                'next' => '/wiki/api/v2/spaces/59179012/pages?body-format=storage&cursor=cursorId',
            ],
        ]),
        MockResponse::make([
            'results' => [[
                'id' => '589496775',
                'title' => 'Mock Page 2',
                'body' => [
                    'storage' => [
                        'value' => 'This is the page content',
                        'representation' => 'storage',
                    ],
                ],
                'spaceId' => '59179012',
            ]],
            '_links' => [],
        ]),
    ]);

    ConfluenceSilo::setMockClient($mockClient);

    $pageId = '58949677';
    $spaceId = '59179012';

    $files = ConfluenceSilo::list([], true, $spaceId);
    $items = $files->all();
    expect($items[0]->id)->toBe($pageId)
        ->and($items[0]->contentStream())->not->toBeNull()
        ->and($items)->toHaveCount(2);
});

it('can get single file from slack', function () {
    Config::set('silo.slack.api_token', '');

    $mockClient = new MockClient([
        MockResponse::make([
            'ok' => true,
            'file' => [
                'id' => 'FAKEFILEID',
                'name' => 'text.txt',
                'title' => 'text.txt',
                'mimetype' => 'text/plain',
                'size' => 144538,
                'url_private_download' => 'https://test.com/download/text.txt',
            ],
        ]),
        /* File Download Mock */
        MockResponse::make(),
    ]);

    SlackSilo::setMockClient($mockClient);

    $fileId = 'FAKEFILEID';

    $file = SlackSilo::get($fileId, true);
    expect($file->id)->toBe($fileId)
        ->and($file)->toBeInstanceOf(SiloFile::class)
        ->and($file->contentStream())->not()->toBeNull();
});

it('can get single file without content from slack', function () {
    Config::set('silo.slack.api_token', '');

    $mockClient = new MockClient([
        MockResponse::make([
            'ok' => true,
            'file' => [
                'id' => 'FAKEFILEID',
                'name' => 'text.txt',
                'title' => 'text.txt',
                'mimetype' => 'text/plain',
                'size' => 144538,
                'url_private_download' => 'https://test.com/download/text.txt',
            ],
        ]),
    ]);

    SlackSilo::setMockClient($mockClient);

    $fileId = 'FAKEFILEID';

    $file = SlackSilo::get($fileId);
    expect($file->id)->toBe($fileId)
        ->and($file)->toBeInstanceOf(SiloFile::class)
        ->and($file->contentStream())->toBeNull();
});

it('can get file list without file content from slack', function () {
    Config::set('silo.slack.api_token', '');

    $mockClient = new MockClient([
        MockResponse::make([
            'ok' => true,
            'files' => [
                [
                    'id' => 'FAKEFILEID',
                    'name' => 'text.txt',
                    'title' => 'text.txt',
                    'mimetype' => 'text/plain',
                    'size' => 144538,
                    'url_private_download' => 'https://test.com/download/text.txt',
                ],
                [
                    'id' => 'FAKEFILEID2',
                    'name' => 'text2.txt',
                    'title' => 'text2.txt',
                    'mimetype' => 'text/plain',
                    'size' => 144538,
                    'url_private_download' => 'https://test.com/download/text2.txt',
                ]],
            'paging' => [[
                'count' => 100,
                'total' => 2,
                'page' => 1,
                'pages' => 1,
            ]],
        ]),
    ]);

    SlackSilo::setMockClient($mockClient);

    $channelId = 'FAKECHANNELID';

    $files = SlackSilo::list([], false, $channelId)->all();

    expect($files[0]->id)->toBe('FAKEFILEID')
        ->and($files[0]->contentStream())->toBeNull()
        ->and($files)->toHaveCount(2);
});

it('can get file list with file content from slack', function () {

    Config::set('silo.slack.api_token', '');

    $mockClient = new MockClient([
        MockResponse::make([
            'ok' => true,
            'files' => [
                [
                    'id' => 'FAKEFILEID',
                    'name' => 'text.txt',
                    'title' => 'text.txt',
                    'mimetype' => 'text/plain',
                    'size' => 144538,
                    'url_private_download' => 'https://test.com/download/text.txt',
                ],
                [
                    'id' => 'FAKEFILEID2',
                    'name' => 'text2.txt',
                    'title' => 'text2.txt',
                    'mimetype' => 'text/plain',
                    'size' => 144538,
                    'url_private_download' => 'https://test.com/download/text2.txt',
                ]],
            'paging' => [[
                'count' => 100,
                'total' => 2,
                'page' => 1,
                'pages' => 1,
            ]],
        ]),
        /* File Download Mocks */
        MockResponse::make(),
        MockResponse::make(),
    ]);

    SlackSilo::setMockClient($mockClient);

    $channelId = 'FAKECHANNELID';

    $files = SlackSilo::list([], true, $channelId)->all();

    expect($files[0]->id)->toBe('FAKEFILEID')
        ->and($files[0]->contentStream())->not()->toBeNull()
        ->and($files)->toHaveCount(2);
});
