<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema;

use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Schema;
use PHPUnit\Framework\TestCase;

abstract class SchemaValidatorTest extends TestCase
{
    protected function loadSchema(string $specFile) : Schema
    {
        $spec   = Reader::readFromYamlFile($specFile);
        $schema = new Schema($spec->schema);
        $schema->resolveReferences(new ReferenceContext($spec, $specFile));

        return $schema;
    }

    protected function loadRawSchema(string $rawSchema) : Schema
    {
        $spec = Reader::readFromYaml($rawSchema);

        $schema = new Schema($spec->schema);
        $schema->resolveReferences(new ReferenceContext($spec, '/'));

        return $schema;
    }
}
