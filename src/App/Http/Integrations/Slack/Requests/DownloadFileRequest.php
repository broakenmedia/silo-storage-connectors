<?php

namespace Silo\StorageConnectors\App\Http\Integrations\Slack\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DownloadFileRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $downloadLink)
    {
    }

    public function resolveEndpoint(): string
    {
        return $this->downloadLink;
    }
}
