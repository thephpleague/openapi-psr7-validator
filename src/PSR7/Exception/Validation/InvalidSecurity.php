<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Validation;

use OpenAPIValidation\PSR7\OperationAddress;
use Throwable;
use function sprintf;

class InvalidSecurity extends AddressValidationFailed
{
    public static function becauseAuthHeaderValueDoesNotMatchExpectedPattern(string $header, string $pattern, OperationAddress $addr) : self
    {
        $exception          = new static($addr);
        $exception->message = sprintf('Header "%s" should match pattern "%s" for %s', $header, $pattern, $addr);

        return $exception;
    }

    public static function becauseRequestDidNotMatchAnySchema(OperationAddress $addr) : self
    {
        $exception          = new static($addr);
        $exception->message = sprintf('None of security schemas did match for %s', $addr);

        return $exception;
    }

    public static function becauseRequestDidNotMatchSchema(string $securitySchemeName, OperationAddress $addr, ?Throwable $prev = null) : self
    {
        $exception          = new static($addr, $prev);
        $exception->message = sprintf('Security schema "%s" did not match for %s', $securitySchemeName, $addr);

        return $exception;
    }
}
