<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Validation;

use OpenAPIValidation\PSR7\OperationAddress;
use function sprintf;

class InvalidCookies extends AddressValidationFailed
{
    public static function becauseOfMissingRequiredCookie(string $cookieName, OperationAddress $address) : self
    {
        $exception          = new static($address);
        $exception->message = sprintf('Missing required cookie "%s" for %s', $cookieName, $address);

        return $exception;
    }

    public static function becauseValueDoesNotMatchSchema(string $cookieName, string $cookieValue, OperationAddress $address) : self
    {
        $exception          = new static($address);
        $exception->message = sprintf('Value "%s" for cookie "%s" is invalid for %s', $cookieValue, $cookieName, $address);

        return $exception;
    }
}
