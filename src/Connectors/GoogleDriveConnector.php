<?php

namespace Silo\StorageConnectors\Connectors;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Silo\StorageConnectors\Contracts\StorageConnectorInterface;
use Silo\StorageConnectors\DTO\StorageResponse;

class GoogleDriveConnector implements StorageConnectorInterface
{

    private Client $client;
    private Drive $service;

    private static array $mimeTypeMap = [
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
        $this->client = new Client();
        $this->client->setAuthConfig(config('silo.google_drive.service_account'));
        $this->client->setScopes(Drive::DRIVE);
        $this->service = new Drive($this->client);
    }

    public function get(string $fileId, bool $includeFileContent = false): StorageResponse
    {
        $file = $this->service->files->get($fileId, ['fields' => 'id,kind,name,mimeType,size,exportLinks,fileExtension,webViewLink']);

        $response = new StorageResponse(
            $file->getId(),
            $file->getName(),
            $file->getFileExtension(),
            $file->getMimeType(),
            $file->getSize(),
            null,
            $file
        );

        if ($includeFileContent) {
            $this->hydrateContentStream($file, $response);
        }

        return $response;
    }

    public function list(?string $pageId = null, int $pageSize = 20, bool $includeFileContent = false): Collection
    {
        $files = $this->service->files->listFiles([
            'fields' => 'files(id, kind), nextPageToken',
            'pageSize' => (string)$pageSize,
            'pageToken' => $pageId
        ]);

        $response = collect();
        foreach ($files->getFiles() as $file) {
            $response->push($this->get($file->getId(), $includeFileContent));
        }
        return $response;
    }

    private function hydrateContentStream(DriveFile $file, StorageResponse $response): void
    {
        if ($file->getFileExtension() !== null) {
            $response->setContentStream($this->service->files->get($file->getId(), ['alt' => 'media'])->getBody());
            /** @phpstan-ignore-next-line */
        } else {
            if (Arr::get(self::$mimeTypeMap, $file->getMimeType()) !== null) {
                try {
                    $response->setContentStream($this->service->files->export($file->getId(), self::$mimeTypeMap[$file->getMimeType()], ['alt' => 'media'])->getBody());
                } catch (GoogleServiceException $e) {
                    $errs = collect($e->getErrors());
                    if ($errs->contains('reason', 'exportSizeLimitExceeded')) {
                        $link = $file->exportLinks[self::$mimeTypeMap[$file->getMimeType()]];
                        try {
                            $client = new \GuzzleHttp\Client(['base_uri' => $link]);
                            $guzResponse = $client->request('GET', '/');
                            $response->setContentStream($guzResponse->getBody());
                        } catch (GuzzleException $e) {
                            Log::error($e->getMessage(), ['file' => __FILE__, 'line' => __LINE__]);
                        }
                    } else {
                        Log::error($e->getMessage(), ['file' => __FILE__, 'line' => __LINE__]);
                    }
                }
            }
        }
    }
}
