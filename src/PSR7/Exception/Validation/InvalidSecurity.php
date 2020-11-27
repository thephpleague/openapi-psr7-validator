<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception\Validation;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;

use function sprintf;

class InvalidSecurity extends AddressValidationFailed
{
    public static function becauseAuthHeaderValueDoesNotMatchExpectedPattern(string $header, string $pattern, OperationAddress $addr): self
    {
        $exception          = static::fromAddr($addr);
        $exception->message = sprintf('Header "%s" should match pattern "%s" for %s', $header, $pattern, $addr);

        return $exception;
    }

    public static function becauseRequestDidNotMatchAnySchema(OperationAddress $addr): self
    {
        $exception          = static::fromAddr($addr);
        $exception->message = sprintf('None of security schemas did match for %s', $addr);

        return $exception;
    }

    public static function becauseRequestDidNotMatchSchema(string $securitySchemeName, OperationAddress $addr, ValidationFailed $prev): self
    {
        $exception          = static::fromAddrAndPrev($addr, $prev);
        $exception->message = sprintf('Security schema "%s" did not match for %s', $securitySchemeName, $addr);

        return $exception;
    }
}
