<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Slack\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetFileRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/files.info";
    }

    protected function defaultQuery(): array
    {
        return [
            'file' => $this->id,
        ];
    }
}
