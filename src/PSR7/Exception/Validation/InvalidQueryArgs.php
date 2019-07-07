<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Validation;

use OpenAPIValidation\PSR7\OperationAddress;
use function sprintf;

class InvalidQueryArgs extends AddressValidationFailed
{
    public static function becauseOfMissingRequiredArgument(string $argumentName, OperationAddress $address) : self
    {
        $exception          = new static($address);
        $exception->message = sprintf('Missing required argument "%s" for %s', $argumentName, $address);

        return $exception;
    }

    public static function becauseValueDoesNotMatchSchema(string $argumentName, string $argumentValue, OperationAddress $address) : self
    {
        $exception          = new static($address);
        $exception->message = sprintf('Value "%s" for argument "%s" is invalid for %s', $argumentValue, $argumentName, $address);

        return $exception;
    }

    public static function becauseOfUnexpectedArgumentIsNotAllowed(string $argument, OperationAddress $address) : self
    {
        $exception          = new static($address);
        $exception->message = sprintf('Argument "%s" is not allowed for %s', $argument, $address);

        return $exception;
    }
}
