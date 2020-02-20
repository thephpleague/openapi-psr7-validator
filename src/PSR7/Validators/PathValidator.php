<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators;

use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidPath;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

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
        if (! ($message instanceof RequestInterface)) {
            return;
        }

        $this->validateRequest($addr, $message);
    }

    /**
     * @throws InvalidPath
     * @throws NoPath
     */
    private function validateRequest(OperationAddress $addr, RequestInterface $message) : void
    {
        $specs = $this->finder->findPathSpecs($addr);

        $path             = $message->getUri()->getPath();
        $pathParsedParams = $addr->parseParams($path); // ['id'=>12]
        $validator        = new SchemaValidator($this->detectValidationStrategy($message));

        foreach ($pathParsedParams as $name => $value) {
            $parameter = SerializedParameter::fromSpec($specs[$name]);
            try {
                $validator->validate($parameter->deserialize($value), $parameter->getSchema());
            } catch (SchemaMismatch $e) {
                throw InvalidPath::becauseValueDoesNotMatchSchema($name, (string) $value, $addr, $e);
            }
        }
    }
}
