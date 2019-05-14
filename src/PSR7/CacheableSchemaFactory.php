<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

interface CacheableSchemaFactory extends SchemaFactory
{
    public function getCacheKey() : string;
}
