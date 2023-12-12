<?php

namespace Silo\StorageConnectors\DTO;

use Psr\Http\Message\StreamInterface;

class SiloFile
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $extension,
        public string $mimeType,
        public ?string $size,
        public ?StreamInterface $contentStream = null,
        private readonly mixed $nativeResponseObject = null
    ) {
    }

    /**
     * Will return the native response object from the storage provider. For example from GoogleDrive it will be a DriveFile
     */
    public function getNativeResponseObject(): mixed
    {
        return $this->nativeResponseObject;
    }

    public function contentStream(): ?StreamInterface
    {
        return $this->contentStream;
    }

    public function setContentStream(StreamInterface $contentStream): void
    {
        $this->contentStream = $contentStream;
    }
}
