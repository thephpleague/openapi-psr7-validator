<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators\CookiesValidator;

use cebe\openapi\spec\Parameter;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\Cookies;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidCookies;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\Validators\SerializedParameter;
use League\OpenAPIValidation\PSR7\Validators\ValidationStrategy;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
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
    public function validate(OperationAddress $addr, MessageInterface $message): void
    {
        Assert::isInstanceOf($message, RequestInterface::class);
        $cookies = Cookies::fromRequest($message);
        $this->checkRequiredCookies($cookies, $addr);
        $this->checkCookiesAgainstSchema($message, $addr, $cookies);
    }

    /**
     * @throws InvalidCookies
     */
    private function checkRequiredCookies(Cookies $cookies, OperationAddress $addr): void
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
    private function checkCookiesAgainstSchema(RequestInterface $request, OperationAddress $addr, Cookies $cookies): void
    {
        $validator = new SchemaValidator($this->detectValidationStrategy($request));

        foreach ($cookies->getAll() as $cookie) {
            /** @var Cookie $cookie */
            if (! isset($this->specs[$cookie->getName()])) {
                continue;
            }

            $parameter = SerializedParameter::fromSpec($this->specs[$cookie->getName()]);
            try {
                $validator->validate($parameter->deserialize($cookie->getValue()), $parameter->getSchema());
            } catch (SchemaMismatch $e) {
                throw InvalidCookies::becauseValueDoesNotMatchSchema($cookie->getName(), $cookie->getValue(), $addr, $e);
            }
        }
    }
}
