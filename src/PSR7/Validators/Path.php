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

class Path
{
    use ValidationStrategy;

    /**
     * @param MessageInterface $message
     * @param Parameter[] $specs [paramName=>Parameter]
     * @param string $pathPattern like "/users/{id}"
     */
    public function validate(MessageInterface $message, array $specs, string $pathPattern): void
    {
        // Note: Determines whether this parameter is mandatory. If the parameter location is "path", this property is REQUIRED and its value MUST be true

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
        $path             = $message->getUri()->getPath();
        $pathParsedParams = PathAddress::parseParams($pathPattern, $path); # ['id'=>12]

        // Check if params are invalid
        foreach ($pathParsedParams as $name => $value) {
            $validator = new SchemaValidator($specs[$name]->schema, $value, $this->detectValidationStrategy($message));
            $validator->validate();
        }
    }
}