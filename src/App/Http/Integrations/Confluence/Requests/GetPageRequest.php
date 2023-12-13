<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Confluence\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetPageRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $id, private readonly bool $includeFileContent = false)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/pages/$this->id";
    }

    protected function defaultQuery(): array
    {
        return [
            'body-format' => $this->includeFileContent ? 'storage' : null,
        ];
    }
}
