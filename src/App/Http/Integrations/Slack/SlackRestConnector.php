<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Slack;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\PaginationPlugin\PagedPaginator;

class SlackRestConnector extends Connector implements HasPagination
{
    public function __construct()
    {
    }

    public function resolveBaseUrl(): string
    {
        return 'https://slack.com/api';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator(config('silo.slack.api_token'));
    }

    public function paginate(Request $request): PagedPaginator
    {
        return new class(connector: $this, request: $request) extends PagedPaginator
        {
            protected function isLastPage(Response $response): bool
            {
                return $response->json('paging.page') >= $response->json('paging.pages');
            }

            protected function getPageItems(Response $response, Request $request): array
            {
                return $response->json('files', []);
            }

            protected function applyPagination(Request $request): Request
            {
                $request->query()->add('page', $this->currentPage);

                if (isset($this->perPageLimit)) {
                    $request->query()->add('count', $this->perPageLimit);
                }

                return $request;
            }
        };
    }
}
