<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators\CookiesValidator;

use cebe\openapi\spec\Parameter;
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

use function array_key_exists;
use function explode;
use function implode;

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
        $cookies = $this->getCookiesFromMessage($message);
        $this->checkRequiredCookies($cookies, $addr);
        $this->checkCookiesAgainstSchema($message, $addr, $cookies);
    }

    /**
     * @param string[] $cookies
     *
     * @throws InvalidCookies
     */
    private function checkRequiredCookies(array $cookies, OperationAddress $addr): void
    {
        foreach ($this->specs as $cookieName => $spec) {
            if ($spec->required && ! array_key_exists($cookieName, $cookies)) {
                throw InvalidCookies::becauseOfMissingRequiredCookie($cookieName, $addr);
            }
        }
    }

    /**
     * @param string[] $cookies
     *
     * @throws InvalidCookies
     */
    private function checkCookiesAgainstSchema(RequestInterface $request, OperationAddress $addr, array $cookies): void
    {
        $validator = new SchemaValidator($this->detectValidationStrategy($request));

        foreach ($cookies as $cookieName => $cookie) {
            if (! isset($this->specs[$cookieName])) {
                continue;
            }

            $parameter = SerializedParameter::fromSpec($this->specs[$cookieName]);
            try {
                $validator->validate($parameter->deserialize($cookie), $parameter->getSchema());
            } catch (SchemaMismatch $e) {
                throw InvalidCookies::becauseValueDoesNotMatchSchema($cookieName, $cookie, $addr, $e);
            }
        }
    }

    /**
     * @return string[]
     */
    private function getCookiesFromMessage(MessageInterface $message): array
    {
        // Needed in case it is an array of cookies.
        $cookieString  = implode('; ', $message->getHeader('Cookie'));
        $headerCookies = explode('; ', $cookieString);

        $cookies = [];
        foreach ($headerCookies as $itm) {
            $pairParts              = explode('=', $itm, 2);
            $pairParts[1]           = $pairParts[1] ?? '';
            $cookies[$pairParts[0]] = $pairParts[1];
        }

        return $cookies;
    }
}
