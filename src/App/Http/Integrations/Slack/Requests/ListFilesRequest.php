<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Slack\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class ListFilesRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $channelId, private readonly array $extraArgs = [])
    {
    }

    public function resolveEndpoint(): string
    {
        return "/files.list";
    }

    protected function defaultQuery(): array
    {
        return array_merge([
            'channel' => $this->channelId,
        ], $this->extraArgs);
    }
}
