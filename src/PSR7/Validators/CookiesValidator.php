<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Parameter;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidCookies;
use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\SpecFinder;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_key_exists;

final class CookiesValidator implements MessageValidator
{
    use ValidationStrategy;

    /** @var SpecFinder */
    private $finder;

    public function __construct(SpecFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function validate(OperationAddress $addr, MessageInterface $message) : void
    {
        $specs = $this->finder->findCookieSpecs($addr);

        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($addr, $message, $specs);
        }
        // TODO should implement validation for Response/Request classes
    }

    /**
     * @param Parameter[] $specs
     *
     * @throws InvalidCookies
     */
    private function validateServerRequest(OperationAddress $addr, ServerRequestInterface $message, array $specs) : void
    {
        // Check if message misses cookies
        foreach ($specs as $cookieName => $spec) {
            if ($spec->required && ! array_key_exists($cookieName, $message->getCookieParams())) {
                throw InvalidCookies::becauseOfMissingRequiredCookie($cookieName, $addr);
            }
        }

        // Check if cookies are invalid
        foreach ($message->getCookieParams() as $cookieName => $cookieValue) {
            // Skip checking for non-described cookie (allow any non described cookies)
            if (! isset($specs[$cookieName])) {
                continue;
            }

            $validator = new SchemaValidator($this->detectValidationStrategy($message));
            try {
                $validator->validate($cookieValue, $specs[$cookieName]->schema);
            } catch (SchemaMismatch $e) {
                throw InvalidCookies::becauseValueDoesNotMatchSchema($cookieName, $cookieValue, $addr);
            }
        }
    }
}
