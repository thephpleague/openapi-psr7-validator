<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Parameter;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidQueryArgs;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use League\OpenAPIValidation\Schema\BreadCrumb;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use const JSON_ERROR_NONE;
use function array_key_exists;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function parse_str;
use function preg_match;

/**
 * @see https://swagger.io/docs/specification/describing-parameters/
 */
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
        if (! $message instanceof RequestInterface) {
            return;
        }

        $validationStrategy   = $this->detectValidationStrategy($message);
        $parsedQueryArguments = $this->parseQueryArguments($message);
        $this->validateQueryArguments($addr, $parsedQueryArguments, $validationStrategy);
    }

    /**
     * @param mixed[] $parsedQueryArguments [limit=>10]
     *
     * @throws InvalidQueryArgs
     * @throws NoPath
     */
    private function validateQueryArguments(OperationAddress $addr, array $parsedQueryArguments, int $validationStrategy) : void
    {
        $specs = $this->finder->findQuerySpecs($addr);
        $this->checkMissingArguments($addr, $parsedQueryArguments, $specs);
        $this->validateAgainstSchema($addr, $parsedQueryArguments, $validationStrategy, $specs);
    }

    /**
     * @param mixed[]     $parsedQueryArguments [limit=>10]
     * @param Parameter[] $specs
     */
    private function checkMissingArguments(OperationAddress $addr, array $parsedQueryArguments, array $specs) : void
    {
        foreach ($specs as $name => $spec) {
            if ($spec->required && ! array_key_exists($name, $parsedQueryArguments)) {
                throw InvalidQueryArgs::becauseOfMissingRequiredArgument($name, $addr);
            }
        }
    }

    /**
     * @param mixed[]     $parsedQueryArguments [limit=>10]
     * @param Parameter[] $specs
     *
     * @throws InvalidQueryArgs
     */
    private function validateAgainstSchema(OperationAddress $addr, array $parsedQueryArguments, int $validationStrategy, array $specs) : void
    {
        // Note: By default, OpenAPI treats all request parameters as optional.

        foreach ($parsedQueryArguments as $name => $argumentValue) {
            // skip if there is no spec for this argument
            if (! array_key_exists($name, $specs)) {
                continue;
            }

            $data      = $argumentValue;
            $parameter = $specs[$name];
            $schema    = $parameter->schema;
            if ($schema === null) {
                // There should be one and only one entry in 'content'.
                foreach ($parameter->content as $contentType => $mediaType) {
                    $schema = $mediaType->schema;
                    $data   = self::parseArgumentValue($argumentValue, $contentType, $addr, $name);
                }
            }

            $validator = new SchemaValidator($validationStrategy);
            try {
                $validator->validate($data, $schema, new BreadCrumb($name));
            } catch (SchemaMismatch $e) {
                throw InvalidQueryArgs::becauseValueDoesNotMatchSchema($name, $argumentValue, $addr, $e);
            }
        }
    }

    /**
     * @return mixed[] like [offset => 10]
     */
    private function parseQueryArguments(RequestInterface $message) : array
    {
        if ($message instanceof ServerRequestInterface) {
            $parsedQueryArguments = $message->getQueryParams();
        } else {
            parse_str($message->getUri()->getQuery(), $parsedQueryArguments);
        }

        return $parsedQueryArguments;
    }

    /**
     * @return mixed
     *
     * @throws InvalidQueryArgs
     */
    private static function parseArgumentValue(string $value, string $contentType, OperationAddress $addr, string $argumentName)
    {
        if (preg_match('#^application/.*json$#', $contentType)) {
            $value = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw InvalidQueryArgs::becauseValueIsNotValidJson(json_last_error_msg(), $argumentName, $addr);
            }
        }

        return $value;
    }
}
