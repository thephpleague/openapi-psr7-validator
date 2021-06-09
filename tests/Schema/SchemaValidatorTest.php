<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema;

use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Schema;
use PHPUnit\Framework\TestCase;

abstract class SchemaValidatorTest extends TestCase
{
    protected function loadRawSchema(string $rawSchema): Schema
    {
        $spec = Reader::readFromYaml($rawSchema, Parameter::class);
        $spec->resolveReferences(new ReferenceContext($spec, '/'));

        return $spec->schema;
    }
}
