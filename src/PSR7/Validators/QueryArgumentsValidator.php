<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidQueryArgs;
use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\SpecFinder;
use OpenAPIValidation\Schema\BreadCrumb;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_key_exists;
use function parse_str;

final class QueryArgumentsValidator implements MessageValidator
{
    use ValidationStrategy;

    /** @var SpecFinder */
    private $finder;

    public function __construct(SpecFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function validate(OperationAddress $addr, MessageInterface $message) : void
    {
        // Note: By default, OpenAPI treats all request parameters as optional.

        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($addr, $message);
        }
        // TODO should implement validation for Request classes
    }

    /**
     * @throws InvalidQueryArgs
     * @throws NoPath
     */
    private function validateServerRequest(OperationAddress $addr, ServerRequestInterface $message) : void
    {
        $specs = $this->finder->findQuerySpecs($addr);

        // Check if message misses query argument
        foreach ($specs as $name => $spec) {
            if ($spec->required && ! array_key_exists($name, $message->getQueryParams())) {
                throw InvalidQueryArgs::becauseOfMissingRequiredArgument($name, $addr);
            }
        }

        // Check if query arguments are invalid
        parse_str($message->getUri()->getQuery(), $parsedQueryArguments);
        foreach ($parsedQueryArguments as $name => $argumentValue) {
            // skip if there are no schema for this argument
            if (! array_key_exists($name, $specs)) {
                // todo: maybe make this optional
                continue;
            }

            $validator = new SchemaValidator($this->detectValidationStrategy($message));
            try {
                $validator->validate($argumentValue, $specs[$name]->schema, new BreadCrumb($name));
            } catch (SchemaMismatch $e) {
                throw InvalidQueryArgs::becauseValueDoesNotMatchSchema($name, $argumentValue, $addr);
            }
        }
    }
}
