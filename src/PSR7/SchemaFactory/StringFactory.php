<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\SchemaFactory;

use OpenAPIValidation\PSR7\CacheableSchemaFactory;
use function crc32;

abstract class StringFactory implements CacheableSchemaFactory
{
    /** @var string */
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getCacheKey() : string
    {
        return 'openapi_' . crc32($this->getContent());
    }

    protected function getContent() : string
    {
        return $this->content;
    }
}
