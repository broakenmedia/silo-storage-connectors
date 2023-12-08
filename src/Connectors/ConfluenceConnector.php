<?php

namespace Silo\StorageConnectors\Connectors;

use Exception;
use Google\Service\Drive;
use GuzzleHttp\Psr7;
use Illuminate\Support\Collection;
use RuntimeException;
use Silo\StorageConnectors\App\Http\Integrations\Confluence\ConfluenceRestConnector;
use Silo\StorageConnectors\App\Http\Integrations\Confluence\Requests\GetPageRequest;
use Silo\StorageConnectors\Contracts\StorageConnectorInterface;
use Silo\StorageConnectors\DTO\StorageResponse;
use Silo\StorageConnectors\Enums\SiloConnector;
use Silo\StorageConnectors\Exceptions\StorageException;

class ConfluenceConnector implements StorageConnectorInterface
{
    private ConfluenceRestConnector $client;

    private Drive $service;

    public function __construct()
    {
        if (config('silo.confluence.api_token') === null) {
            throw new RuntimeException('silo.confluence.api_token is not set');
        }
        $this->client = new ConfluenceRestConnector('https://rawnet-partner.atlassian.net');
    }

    /**
     * @throws StorageException
     */
    public function get(string $resourceId, bool $includeFileContent = false): StorageResponse
    {
        try {
            $r = $this->client->send(new GetPageRequest($resourceId));
            $file = $r->json();

            return new StorageResponse(
                $file['id'],
                $file['title'],
                'html',
                'text/html',
                strlen($file['body']['storage']['value']),
                $includeFileContent ? Psr7\Utils::streamFor($file['body']['storage']['value']) : null,
                $file
            );
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), SiloConnector::CONFLUENCE, $e->getCode(), $e);
        }
    }

    public function list(?string $pageId = null, int $pageSize = 20, bool $includeFileContent = false, array $extraArgs = []): Collection
    {

        return $response;
    }
}
