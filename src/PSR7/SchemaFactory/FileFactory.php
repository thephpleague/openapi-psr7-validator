<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\SchemaFactory;

use League\OpenAPIValidation\PSR7\CacheableSchemaFactory;
use Webmozart\Assert\Assert;

use function crc32;
use function realpath;

abstract class FileFactory implements CacheableSchemaFactory
{
    /** @var string */
    private $filename;

    public function __construct(string $filename)
    {
        Assert::file($filename);

        $this->filename = $filename;
    }

    public function getCacheKey(): string
    {
        return 'openapi_' . crc32(realpath($this->getFilename()));
    }

    protected function getFilename(): string
    {
        return $this->filename;
    }
}
