<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Validation;

use OpenAPIValidation\PSR7\OperationAddress;
use function sprintf;

class InvalidHeaders extends AddressValidationFailed
{
    public static function becauseOfMissingRequiredHeader(string $headerName, OperationAddress $address) : self
    {
        $exception          = new static($address);
        $exception->message = sprintf('Missing required header "%s" for %s', $headerName, $address);

        return $exception;
    }

    public static function becauseValueDoesNotMatchSchema(string $headerName, string $headerValue, OperationAddress $address) : self
    {
        $exception          = new static($address);
        $exception->message = sprintf('Value "%s" for header "%s" is invalid for %s', $headerValue, $headerName, $address);

        return $exception;
    }

    public static function becauseOfUnexpectedHeaderIsNotAllowed(string $header, OperationAddress $address) : self
    {
        $exception          = new static($address);
        $exception->message = sprintf('Header "%s" is not allowed for %s', $header, $address);

        return $exception;
    }
}
