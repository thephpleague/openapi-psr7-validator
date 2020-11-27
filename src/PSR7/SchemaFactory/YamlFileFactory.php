<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\SchemaFactory;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;

use function realpath;

final class YamlFileFactory extends FileFactory
{
    public function createSchema(): OpenApi
    {
        return Reader::readFromYamlFile(
            realpath($this->getFilename())
        );
    }
}
