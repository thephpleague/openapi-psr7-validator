<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators;

use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidParameter;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidPath;
use League\OpenAPIValidation\PSR7\Exception\Validation\RequiredParameterMissing;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use LogicException;
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
    public function validate(OperationAddress $addr, MessageInterface $message): void
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
    private function validateRequest(OperationAddress $addr, RequestInterface $message): void
    {
        $validator        = new ArrayValidator($this->finder->findOperationAndPathLevelSpecs($addr));
        $path             = $message->getUri()->getPath();
        $pathParsedParams = $addr->parseParams($path); // ['id'=>12]

        try {
            $validator->validateArray($pathParsedParams, $this->detectValidationStrategy($message));
        } catch (InvalidParameter $e) {
            throw InvalidPath::becauseValueDoesNotMatchSchema($e->name(), $e->value(), $addr, $e);
        } catch (RequiredParameterMissing $e) {
            throw new LogicException('RequiredParameterMissing should not be thrown in PathValidator, ' .
                'because presence of all parameters have to be checked before', 0, $e);
        }
    }
}
