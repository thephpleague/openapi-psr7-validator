<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\SchemaFactory;

use cebe\openapi\spec\OpenApi;
use OpenAPIValidation\PSR7\SchemaFactory;

final class PrecreatedSchemaFactory implements SchemaFactory
{
    /** @var OpenApi */
    private $schema;

    public function __construct(OpenApi $schema)
    {
        $this->schema = $schema;
    }

    public function createSchema() : OpenApi
    {
        return $this->schema;
    }
}
