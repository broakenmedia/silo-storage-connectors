<?php

namespace Silo\StorageConnectors\Exceptions;

use Silo\StorageConnectors\Enums\SiloConnector;
use Throwable;

class StorageException extends \Exception
{
    protected ?SiloConnector $connectorType;

    /**
     * Constructor for StorageException.
     *
     * @param string $message The Exception message.
     * @param ?SiloConnector $connectorType The type of storage connector related to the exception.
     * @param int $code The Exception code.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(string $message, ?SiloConnector $connectorType = null, int $code = 0, Throwable $previous = null)
    {
        $this->connectorType = $connectorType;
        parent::__construct($message, $code, $previous);
    }

    public function getConnectorType(): SiloConnector
    {
        return $this->connectorType;
    }
}
