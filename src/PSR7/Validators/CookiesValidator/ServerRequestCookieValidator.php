<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators\CookiesValidator;

use cebe\openapi\spec\Parameter;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidCookies;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\Validators\ValidationStrategy;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;
use function array_key_exists;

class ServerRequestCookieValidator implements MessageValidator
{
    use ValidationStrategy;

    /** @var Parameter[] */
    private $specs;

    /**
     * @param Parameter[] $specs
     */
    public function __construct(array $specs)
    {
        $this->specs = $specs;
    }

    /**
     * @throws InvalidCookies
     */
    public function validate(OperationAddress $addr, MessageInterface $message) : void
    {
        Assert::isInstanceOf($message, ServerRequestInterface::class);
        $this->checkRequiredCookies($addr, $message);
        $this->checkCookiesAgainstSchema($addr, $message);
    }

    /**
     * @throws InvalidCookies
     */
    private function checkRequiredCookies(OperationAddress $addr, ServerRequestInterface $message) : void
    {
        foreach ($this->specs as $cookieName => $spec) {
            if ($spec->required && ! array_key_exists($cookieName, $message->getCookieParams())) {
                throw InvalidCookies::becauseOfMissingRequiredCookie($cookieName, $addr);
            }
        }
    }

    /**
     * @throws InvalidCookies
     */
    private function checkCookiesAgainstSchema(OperationAddress $addr, ServerRequestInterface $message) : void
    {
        foreach ($message->getCookieParams() as $cookieName => $cookieValue) {
            if (! isset($this->specs[$cookieName])) {
                continue;
            }

            $validator = new SchemaValidator($this->detectValidationStrategy($message));
            try {
                $validator->validate($cookieValue, $this->specs[$cookieName]->schema);
            } catch (SchemaMismatch $e) {
                throw InvalidCookies::becauseValueDoesNotMatchSchema($cookieName, $cookieValue, $addr, $e);
            }
        }
    }
}
