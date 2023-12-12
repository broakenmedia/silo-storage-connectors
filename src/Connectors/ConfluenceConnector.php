<?php

namespace Silo\StorageConnectors\Connectors;

use Exception;
use GuzzleHttp\Psr7;
use Illuminate\Support\Enumerable;
use RuntimeException;
use Silo\StorageConnectors\App\Http\Integrations\Confluence\ConfluenceRestConnector;
use Silo\StorageConnectors\App\Http\Integrations\Confluence\Requests\GetPageRequest;
use Silo\StorageConnectors\App\Http\Integrations\Confluence\Requests\GetSpacePagesRequest;
use Silo\StorageConnectors\Contracts\StorageConnectorInterface;
use Silo\StorageConnectors\DTO\SiloFile;
use Silo\StorageConnectors\Enums\SiloConnector;
use Silo\StorageConnectors\Exceptions\StorageException;

class ConfluenceConnector implements StorageConnectorInterface
{
    private ConfluenceRestConnector $client;

    public function __construct()
    {
        if (config('silo.confluence.domain') === null) {
            throw new RuntimeException('silo.confluence.domain is not set');
        }

        if (config('silo.confluence.username') === null) {
            throw new RuntimeException('silo.confluence.username is not set');
        }

        if (config('silo.confluence.api_token') === null) {
            throw new RuntimeException('silo.confluence.api_token is not set');
        }

        $this->client = new ConfluenceRestConnector(config('silo.confluence.domain'));
    }

    /**
     * @param  string  $resourceId The confluence page ID
     *
     * @throws StorageException
     */
    public function get(string $resourceId, bool $includeFileContent = false): SiloFile
    {
        try {
            $r = $this->client->send(new GetPageRequest($resourceId));
            $file = $r->json();

            return new SiloFile(
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

    /**
     * @throws StorageException
     */
    public function list(bool $includeFileContent = false, array $extraArgs = [], ?string $spaceId = null): Enumerable
    {
        if ($spaceId === null) {
            throw new RuntimeException('Confluence List requires a spaceId');
        }

        try {
            $r = $this->client->paginate(new GetSpacePagesRequest($spaceId));

            return $r->collect()->map(function (array $file) use ($includeFileContent) {
                return new SiloFile(
                    $file['id'],
                    $file['title'],
                    'html',
                    'text/html',
                    strlen($file['body']['storage']['value']),
                    $includeFileContent ? Psr7\Utils::streamFor($file['body']['storage']['value']) : null,
                    $file);
            });
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), SiloConnector::CONFLUENCE, $e->getCode(), $e);
        }
    }
}
