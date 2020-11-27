<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception\Validation;

use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;

use function sprintf;

class InvalidCookies extends AddressValidationFailed
{
    public static function becauseOfMissingRequiredCookie(string $cookieName, OperationAddress $address): self
    {
        $exception          = static::fromAddr($address);
        $exception->message = sprintf('Missing required cookie "%s" for %s', $cookieName, $address);

        return $exception;
    }

    public static function becauseValueDoesNotMatchSchema(string $cookieName, string $cookieValue, OperationAddress $address, SchemaMismatch $prev): self
    {
        $exception          = static::fromAddrAndPrev($address, $prev);
        $exception->message = sprintf('Value "%s" for cookie "%s" is invalid for %s', $cookieValue, $cookieName, $address);

        return $exception;
    }
}
