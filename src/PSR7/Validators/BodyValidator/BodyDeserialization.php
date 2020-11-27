<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators\BodyValidator;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type as CebeType;
use League\OpenAPIValidation\PSR7\Validators\SerializedParameter;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;

trait BodyDeserialization
{
    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed> $body
     *
     * @throws SchemaMismatch
     */
    protected function deserializeBody(array $body, Schema $schema): array
    {
        if ($schema->type !== CebeType::OBJECT) {
            return $body;
        }

        foreach ($schema->properties as $propName => $propSchema) {
            if (! isset($body[$propName])) {
                continue;
            }

            $param           = new SerializedParameter($propSchema);
            $body[$propName] = $param->deserialize($body[$propName]);
        }

        return $body;
    }
}
