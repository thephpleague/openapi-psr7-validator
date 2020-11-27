<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators;

use League\OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

trait ValidationStrategy
{
    /**
     * Distinguish requests and responses, so we can treat them differently (writeOnly/readOnly OAS keywords)
     */
    protected function detectValidationStrategy(MessageInterface $message): int
    {
        if ($message instanceof ResponseInterface) {
            return SchemaValidator::VALIDATE_AS_RESPONSE;
        }

        return SchemaValidator::VALIDATE_AS_REQUEST;
    }
}
