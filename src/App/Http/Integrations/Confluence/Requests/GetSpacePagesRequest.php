<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Confluence\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetSpacePagesRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $id, private readonly array $extraArgs = [])
    {
    }

    public function resolveEndpoint(): string
    {
        return "/spaces/$this->id/pages";
    }

    protected function defaultQuery(): array
    {
        return array_merge([
            'body-format' => 'storage',
        ], $this->extraArgs);

    }
}
