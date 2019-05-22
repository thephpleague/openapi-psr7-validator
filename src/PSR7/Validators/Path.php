<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Parameter;
use OpenAPIValidation\PSR7\PathAddress;
use OpenAPIValidation\Schema\Exception\KeywordMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

class Path
{
    use ValidationStrategy;

    /**
     * @param Parameter[] $specs       [paramName=>Parameter]
     * @param string      $pathPattern like "/users/{id}"
     *
     * @throws KeywordMismatch
     */
    public function validate(MessageInterface $message, array $specs, string $pathPattern) : void
    {
        // Note: Determines whether this parameter is mandatory. If the parameter location is "path", this property is REQUIRED and its value MUST be true

        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($message, $specs, $pathPattern);
        }

        // TODO should implement validation for Request classes
    }

    /**
     * @param Parameter[] $specs
     *
     * @throws KeywordMismatch
     */
    private function validateServerRequest(ServerRequestInterface $message, array $specs, string $pathPattern) : void
    {
        $path             = $message->getUri()->getPath();
        $pathParsedParams = PathAddress::parseParams($pathPattern, $path); // ['id'=>12]

        $validator = new SchemaValidator($this->detectValidationStrategy($message));

        // Check if params are invalid
        foreach ($pathParsedParams as $name => $value) {
            $validator->validate($value, $specs[$name]->schema);
        }
    }
}
