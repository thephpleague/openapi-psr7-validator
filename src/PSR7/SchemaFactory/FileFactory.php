<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\SchemaFactory;

use OpenAPIValidation\PSR7\CacheableSchemaFactory;
use function crc32;
use function realpath;

abstract class FileFactory implements CacheableSchemaFactory
{
    /** @var string */
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function getCacheKey() : string
    {
        return 'openapi_' . crc32(realpath($this->getFilename()));
    }

    protected function getFilename() : string
    {
        return $this->filename;
    }
}
