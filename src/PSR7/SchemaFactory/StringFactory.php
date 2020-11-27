<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\SchemaFactory;

use League\OpenAPIValidation\PSR7\CacheableSchemaFactory;

use function hash;

abstract class StringFactory implements CacheableSchemaFactory
{
    /** @var string */
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getCacheKey(): string
    {
        return 'openapi_' . hash('crc32b', $this->getContent());
    }

    protected function getContent(): string
    {
        return $this->content;
    }
}
