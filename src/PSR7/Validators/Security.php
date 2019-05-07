<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Validators;


use cebe\openapi\spec\Parameter;
use OpenAPIValidation\PSR7\PathAddress;
use OpenAPIValidation\Schema\Validator as SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

class Security
{
    use ValidationStrategy;

    /**
     * @param MessageInterface $message
     * @param Parameter[] $specs [paramName=>Parameter]
     * @param string $pathPattern like "/users/{id}"
     */
    public function validate(MessageInterface $message, array $specs, string $pathPattern): void
    {
        // Note: Security schemes support OR/AND union
        // That is, security is an array of hashmaps, where each hashmap contains one or more named security schemes.
        // Items in a hashmap are combined using logical AND, and array items are combined using logical OR.
        // Security schemes combined via OR are alternatives – any one can be used in the given context.
        // Security schemes combined via AND must be used simultaneously in the same request.


        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($message, $specs, $pathPattern);
        }

        // TODO should implement validation for Request classes
    }

    /**
     * @param ServerRequestInterface $message
     * @param Parameter[] $specs
     * @param string $pathPattern
     * @throws \Exception
     */
    private function validateServerRequest(ServerRequestInterface $message, array $specs, string $pathPattern)
    {
        // TODO
    }
}