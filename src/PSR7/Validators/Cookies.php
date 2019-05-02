<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Validators;


use cebe\openapi\spec\Parameter;
use OpenAPIValidation\Schema\Validator as SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cookies
{
    use ValidationStrategy;

    /**
     * @param MessageInterface $message
     * @param Parameter[] $specs [cookie_name=>schema]
     */
    public function validate(MessageInterface $message, array $specs): void
    {

        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($message, $specs);
        }

        // TODO should implement validation for Response/Request classes

    }

    /**
     * @param ServerRequestInterface $message
     * @param Parameter[] $specs
     */
    private function validateServerRequest(ServerRequestInterface $message, array $specs)
    {
        // Check if message misses cookies
        foreach ($specs as $cookieName => $spec) {
            if (!array_key_exists($cookieName, $message->getCookieParams()) && $spec->required) {
                throw new \RuntimeException($cookieName, 301);
            }
        }

        // Check if cookies are invalid
        foreach ($message->getCookieParams() as $cookieName => $cookieValue) {
            $validator = new SchemaValidator($specs[$cookieName]->schema, $cookieValue, $this->detectValidationStrategy($message));
            $validator->validate();
        }
    }
}