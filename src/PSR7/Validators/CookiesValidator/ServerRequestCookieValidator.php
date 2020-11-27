<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators\CookiesValidator;

use cebe\openapi\spec\Parameter;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidCookies;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidParameter;
use League\OpenAPIValidation\PSR7\Exception\Validation\RequiredParameterMissing;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\Validators\ArrayValidator;
use League\OpenAPIValidation\PSR7\Validators\ValidationStrategy;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

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
    public function validate(OperationAddress $addr, MessageInterface $message): void
    {
        Assert::isInstanceOf($message, ServerRequestInterface::class);
        $validator = new ArrayValidator($this->specs);

        try {
            $validator->validateArray($message->getCookieParams(), $this->detectValidationStrategy($message));
        } catch (RequiredParameterMissing $e) {
            throw InvalidCookies::becauseOfMissingRequiredCookie($e->name(), $addr);
        } catch (InvalidParameter $e) {
            throw InvalidCookies::becauseValueDoesNotMatchSchema($e->name(), $e->value(), $addr, $e->getPrevious());
        }
    }
}
