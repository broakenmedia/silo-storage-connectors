<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Confluence\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetSpaceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/spaces/$this->id";
    }
}
