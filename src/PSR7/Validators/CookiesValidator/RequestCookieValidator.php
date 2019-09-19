<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators\CookiesValidator;

use cebe\openapi\spec\Parameter;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\Cookies;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidCookies;
use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\Validators\ValidationStrategy;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Webmozart\Assert\Assert;

class RequestCookieValidator implements MessageValidator
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
        Assert::isInstanceOf($message, RequestInterface::class);
        $cookies = Cookies::fromRequest($message);
        $this->checkRequiredCookies($cookies, $addr);
        $this->checkCookiesAgainstSchema($message, $addr, $cookies);
    }

    /**
     * @throws InvalidCookies
     */
    private function checkRequiredCookies(Cookies $cookies, OperationAddress $addr) : void
    {
        foreach ($this->specs as $cookieName => $spec) {
            if ($spec->required && ! $cookies->has($cookieName)) {
                throw InvalidCookies::becauseOfMissingRequiredCookie($cookieName, $addr);
            }
        }
    }

    /**
     * @throws InvalidCookies
     */
    private function checkCookiesAgainstSchema(RequestInterface $request, OperationAddress $addr, Cookies $cookies) : void
    {
        foreach ($cookies->getAll() as $cookie) {
            /** @var Cookie $cookie */
            if (! isset($this->specs[$cookie->getName()])) {
                continue;
            }

            $validator = new SchemaValidator($this->detectValidationStrategy($request));
            try {
                $validator->validate($cookie->getValue(), $this->specs[$cookie->getName()]->schema);
            } catch (SchemaMismatch $e) {
                throw InvalidCookies::becauseValueDoesNotMatchSchema($cookie->getName(), $cookie->getValue(), $addr, $e);
            }
        }
    }
}
