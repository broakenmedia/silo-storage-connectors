<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Confluence\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetSpacesRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/spaces';
    }
}
