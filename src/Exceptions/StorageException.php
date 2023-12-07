<?php

namespace Silo\StorageConnectors\Exceptions;

use Throwable;

class StorageException extends \Exception
{
    protected string $providerType;

    /**
     * Constructor for StorageException.
     *
     * @param  string  $message The Exception message.
     * @param  string  $providerType The type of storage provider related to the exception.
     * @param  int  $code The Exception code.
     * @param  Throwable|null  $previous The previous throwable used for exception chaining.
     */
    public function __construct(string $message, string $providerType = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->providerType = $providerType;
    }

    public function getProviderType(): string
    {
        return $this->providerType;
    }
}
