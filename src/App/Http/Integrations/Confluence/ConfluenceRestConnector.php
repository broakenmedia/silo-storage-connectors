<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Confluence;

use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;

class ConfluenceRestConnector extends Connector
{

    public function __construct(private readonly string $atlassianDomain)
    {
    }

    public function resolveBaseUrl(): string
    {
        return $this->atlassianDomain . '/wiki/api/v2';
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
}
