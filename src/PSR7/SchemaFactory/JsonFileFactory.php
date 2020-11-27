<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\SchemaFactory;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;

use function realpath;

final class JsonFileFactory extends FileFactory
{
    public function createSchema(): OpenApi
    {
        return Reader::readFromJsonFile(
            realpath($this->getFilename())
        );
    }
}
