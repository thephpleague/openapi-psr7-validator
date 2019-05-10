<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use OpenAPIValidation\Schema\Validator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

trait ValidationStrategy
{
    /**
     * Distinguish requests and responses, so we can treat them differently (writeOnly/readOnly OAS keywords)
     */
    protected function detectValidationStrategy(MessageInterface $message) : int
    {
        if ($message instanceof ResponseInterface) {
            return Validator::VALIDATE_AS_RESPONSE;
        }

        return Validator::VALIDATE_AS_REQUEST;
    }
}
