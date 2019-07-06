<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Parameter;
use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\Schema\BreadCrumb;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_key_exists;
use function parse_str;

class QueryArgumentsValidator
{
    use ValidationStrategy;

    /**
     * @param Parameter[] $specs [queryArgumentName=>schema]
     *
     * @throws SchemaMismatch
     * @throws ValidationFailed
     */
    public function validate(MessageInterface $message, array $specs) : void
    {
        // Note: By default, OpenAPI treats all request parameters as optional.

        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($message, $specs);
        }
        // TODO should implement validation for Request classes
    }

    /**
     * @param Parameter[] $specs
     *
     * @throws SchemaMismatch
     * @throws ValidationFailed
     */
    private function validateServerRequest(ServerRequestInterface $message, array $specs) : void
    {
        // Check if message misses query argument
        foreach ($specs as $name => $spec) {
            if ($spec->required && ! array_key_exists($name, $message->getQueryParams())) {
                throw new ValidationFailed($name, 401);
            }
        }

        // Check if query arguments are invalid
        parse_str($message->getUri()->getQuery(), $parsedQueryArguments);
        foreach ($parsedQueryArguments as $name => $argumentValue) {
            // skip if there are no schema for this argument
            if (! array_key_exists($name, $specs)) {
                continue;
            }

            $validator = new SchemaValidator($this->detectValidationStrategy($message));
            $validator->validate($argumentValue, $specs[$name]->schema, new BreadCrumb($name));
        }
    }
}
