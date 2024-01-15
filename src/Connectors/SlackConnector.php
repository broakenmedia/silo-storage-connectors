<?php

namespace Silo\StorageConnectors\Connectors;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use RuntimeException;
use Saloon\Http\Faking\MockClient;
use Silo\StorageConnectors\App\Http\Integrations\Slack\Requests\DownloadFileRequest;
use Silo\StorageConnectors\App\Http\Integrations\Slack\Requests\GetFileRequest;
use Silo\StorageConnectors\App\Http\Integrations\Slack\Requests\ListFilesRequest;
use Silo\StorageConnectors\App\Http\Integrations\Slack\SlackRestConnector;
use Silo\StorageConnectors\Contracts\StorageConnectorInterface;
use Silo\StorageConnectors\DTO\SiloFile;
use Silo\StorageConnectors\Enums\SiloConnector;
use Silo\StorageConnectors\Exceptions\StorageException;

class SlackConnector implements StorageConnectorInterface
{
    private SlackRestConnector $client;

    public function __construct()
    {
        if (config('silo.slack.api_token') === null) {
            throw new RuntimeException('silo.slack.api_token is not set');
        }

        $this->client = new SlackRestConnector();
    }

    /**
     * @param string $resourceId The slack file ID
     *
     * @throws StorageException
     */
    public function get(string $resourceId, bool $includeFileContent = false): SiloFile
    {
        try {
            $r = $this->client->send(new GetFileRequest($resourceId));
            $file = $r->json();

            $fileContentRequest = new DownloadFileRequest(Arr::get($file, 'file.url_private_download'));

            return new SiloFile(
                Arr::get($file, 'file.id'),
                Arr::get($file, 'file.title'),
                pathinfo(Arr::get($file, 'file.name'), PATHINFO_EXTENSION),
                Arr::get($file, 'file.mimetype'),
                $includeFileContent ? Arr::get($file, 'file.size') : null,
                $includeFileContent ? $this->client->send($fileContentRequest)->stream() : null,
                $file
            );
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), SiloConnector::SLACK, $e->getCode(), $e);
        }
    }

    /**
     * @throws StorageException
     */
    public function list(array $extraArgs = [], bool $includeFileContent = false, ?string $channelId = null): Enumerable
    {
        if ($channelId === null) {
            throw new RuntimeException('Slack List requires a channelId');
        }

        try {
            $r = $this->client->paginate(new ListFilesRequest($channelId, $extraArgs));

            return $r->collect()->map(function (array $file) use ($includeFileContent) {
                $fileContentRequest = new DownloadFileRequest(Arr::get($file, 'url_private_download'));
                return new SiloFile(
                    Arr::get($file, 'id'),
                    Arr::get($file, 'title'),
                    pathinfo(Arr::get($file, 'name'), PATHINFO_EXTENSION),
                    Arr::get($file, 'mimetype'),
                    $includeFileContent ? Arr::get($file, 'size') : null,
                    $includeFileContent ? $this->client->send($fileContentRequest)->stream() : null,
                    $file);
            });
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), SiloConnector::SLACK, $e->getCode(), $e);
        }
    }

    public function setMockClient(MockClient $mockClient): void
    {
        $this->client->withMockClient($mockClient);
    }
}
