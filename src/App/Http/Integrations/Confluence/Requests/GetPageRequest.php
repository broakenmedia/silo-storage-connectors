<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Confluence\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetPageRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/pages/" . $this->id . "?body-format=storage";
    }
}
