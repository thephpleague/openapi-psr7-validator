<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Validators;


use cebe\openapi\spec\Parameter;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

class Path
{

    /**
     * @param MessageInterface $message
     * @param Parameter[] $specs [paramName=>Parameter]
     */
    public function validate(MessageInterface $message, array $specs): void
    {
        // Note: Determines whether this parameter is mandatory. If the parameter location is "path", this property is REQUIRED and its value MUST be true

        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($message, $specs);
        }

        // TODO should implement validation for Request classes
    }

    /**
     * @param ServerRequestInterface $message
     * @param Parameter[] $specs
     */
    private function validateServerRequest(ServerRequestInterface $message, array $specs)
    {



//        // Check if message misses query argument
//        foreach ($specs as $name => $spec) {
//            if (!array_key_exists($name, $message->getUri()->getPath()) && $spec->required) {
//                throw new \RuntimeException($name, 501);
//            }
//        }
//
//        // Check if cookies are invalid
//        foreach ($message->getQueryParams() as $name => $argumentValue) {
//            $validator = new SchemaValidator($specs[$name]->schema, $argumentValue, $this->detectValidationStrategy($message));
//            $validator->validate();
//        }
    }
}