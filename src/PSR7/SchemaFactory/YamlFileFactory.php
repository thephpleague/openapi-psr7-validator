<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\SchemaFactory;

use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use function realpath;

final class YamlFileFactory extends FileFactory
{
    public function createSchema() : OpenApi
    {
        $schema = Reader::readFromYamlFile($this->getFilename());

        $schema->resolveReferences(new ReferenceContext($schema, realpath($this->getFilename())));

        return $schema;
    }
}
