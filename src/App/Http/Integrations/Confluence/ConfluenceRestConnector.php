<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Confluence;

use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\PaginationPlugin\CursorPaginator;

class ConfluenceRestConnector extends Connector implements HasPagination
{
    public function __construct(private readonly string $atlassianDomain)
    {
    }

    public function resolveBaseUrl(): string
    {
        return $this->atlassianDomain.'/wiki/api/v2';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator(config('silo.confluence.username'), config('silo.confluence.api_token'));
    }

    public function paginate(Request $request): CursorPaginator
    {
        return new class(connector: $this, request: $request) extends CursorPaginator
        {
            protected function getNextCursor(Response $response): int|string
            {
                parse_str(parse_url($response->json('_links.next'))['query'], $params);

                return $params['cursor'];
            }

            protected function isLastPage(Response $response): bool
            {
                return is_null($response->json('_links.next'));
            }

            protected function getPageItems(Response $response, Request $request): array
            {
                return $response->json('results');
            }
        };
    }
}
