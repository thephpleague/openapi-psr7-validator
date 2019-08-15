<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidPath;
use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\SpecFinder;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PathValidator implements MessageValidator
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
        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($addr, $message);
        }
        // TODO should implement validation for Request classes
    }

    /**
     * @throws InvalidPath
     * @throws NoPath
     */
    private function validateServerRequest(OperationAddress $addr, ServerRequestInterface $message) : void
    {
        $specs = $this->finder->findPathSpecs($addr);

        $path             = $message->getUri()->getPath();
        $pathParsedParams = $addr->parseParams($path); // ['id'=>12]

        $validator = new SchemaValidator($this->detectValidationStrategy($message));

        // Check if params are invalid
        foreach ($pathParsedParams as $name => $value) {
            try {
                $validator->validate($value, $specs[$name]->schema);
            } catch (SchemaMismatch $e) {
                throw InvalidPath::becauseValueDoesNotMatchSchema($name, (string) $value, $addr, $e);
            }
        }
    }
}
