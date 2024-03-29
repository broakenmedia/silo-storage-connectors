<?php

namespace Silo\StorageConnectors\Connectors;

use Exception;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use RuntimeException;
use Silo\StorageConnectors\Contracts\StorageConnectorInterface;
use Silo\StorageConnectors\DTO\SiloFile;
use Silo\StorageConnectors\Enums\SiloConnector;
use Silo\StorageConnectors\Exceptions\StorageException;

class GoogleDriveConnector implements StorageConnectorInterface
{
    private Drive $service;

    private static array $exportMimeTypeMap = [
        'application/vnd.google-apps.document' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.google-apps.spreadsheet' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.google-apps.presentation' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.google-apps.drawing' => 'image/png',
    ];

    public function __construct()
    {
        if (config('silo.google_drive.service_account') === null) {
            throw new RuntimeException('silo.google_drive.service_account is not set');
        }
        $this->service = app(Drive::class);
    }

    /**
     * @throws StorageException
     */
    public function get(string $resourceId, bool $includeFileContent = false): SiloFile
    {
        try {
            $file = $this->service->files->get($resourceId, ['fields' => 'id,kind,name,mimeType,size,exportLinks,fileExtension,webViewLink']);

            $response = new SiloFile(
                $file->getId(),
                $file->getName(),
                $file->getFileExtension(),
                $file->getMimeType(),
                $file->getSize(),
                null,
                $file
            );

            if ($includeFileContent) {
                try {
                    $this->hydrateContentStream($file, $response);
                } catch (StorageException $e) {
                    Log::error($e, ['file' => __FILE__, 'line' => __LINE__]);
                }
            }
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), SiloConnector::GOOGLE_DRIVE, $e->getCode(), $e);
        }

        return $response;
    }

    private function listFiles(array $extraArgs = []): Drive\FileList
    {
        return $this->service->files->listFiles(array_merge([
            'fields' => 'files(id, kind), nextPageToken',
            'pageSize' => Arr::get($extraArgs, 'pageSize', 20),
            'pageToken' => Arr::get($extraArgs, 'pageToken'),
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ], $extraArgs));
    }

    /**
     * @throws StorageException
     */
    public function list(array $extraArgs = [], bool $includeFileContent = false): Enumerable
    {
        return LazyCollection::make(function () use ($includeFileContent, $extraArgs) {
            $pageToken = null;
            do {
                $extraArgs['pageToken'] = $pageToken;
                $data = $this->listFiles($extraArgs);
                foreach ($data as $file) {
                    yield $this->get($file->getId(), $includeFileContent);
                }
                $pageToken = $data->getNextPageToken();
            } while ($pageToken !== null);
        });
    }

    /**
     * @throws StorageException
     */
    private function hydrateContentStream(DriveFile $file, SiloFile $response): void
    {
        if ($file->getFileExtension() !== null) {
            try {
                $response->setContentStream($this->service->files->get($file->getId(), ['alt' => 'media'])->getBody());
            } catch (GoogleServiceException $e) {
                throw new StorageException($e->getMessage(), SiloConnector::GOOGLE_DRIVE, $e->getCode(), $e);
            }
            /** @phpstan-ignore-next-line */
        } else {
            if (Arr::get(self::$exportMimeTypeMap, $file->getMimeType()) !== null) {
                try {
                    $response->setContentStream($this->service->files->export($file->getId(), self::$exportMimeTypeMap[$file->getMimeType()], ['alt' => 'media'])->getBody());
                } catch (GoogleServiceException $e) {
                    $errs = collect($e->getErrors());
                    if ($errs->contains('reason', 'exportSizeLimitExceeded')) {
                        $link = $file->exportLinks[self::$exportMimeTypeMap[$file->getMimeType()]];
                        try {
                            $client = new \GuzzleHttp\Client(['base_uri' => $link]);
                            $guzResponse = $client->request('GET', '/');
                            $response->setContentStream($guzResponse->getBody());
                        } catch (GuzzleException $e) {
                            throw new StorageException($e->getMessage(), SiloConnector::GOOGLE_DRIVE, $e->getCode(), $e);
                        }
                    } else {
                        throw new StorageException($e->getMessage(), SiloConnector::GOOGLE_DRIVE, $e->getCode(), $e);
                    }
                }
            }
        }
    }

    /**
     * If you want to export a file to a format that is not supported by the default map, you can add it to the custom map.
     * See https://developers.google.com/drive/api/guides/ref-export-formats for export options.
     *
     * If you add a mapping that google do not support it will log an error and the content stream will remain empty.
     *
     * @param  array  $customMap  Custom MIME type map provided by the user.
     */
    public function setExportMimeTypeMap(array $customMap): void
    {
        self::$exportMimeTypeMap = array_merge(self::$exportMimeTypeMap, $customMap);
    }
}
