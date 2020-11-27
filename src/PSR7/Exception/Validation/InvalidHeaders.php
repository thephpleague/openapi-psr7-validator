<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception\Validation;

use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;

use function sprintf;

class InvalidHeaders extends AddressValidationFailed
{
    public static function becauseOfMissingRequiredHeader(string $headerName, OperationAddress $address): self
    {
        $exception          = static::fromAddr($address);
        $exception->message = sprintf('Missing required header "%s" for %s', $headerName, $address);

        return $exception;
    }

    public static function becauseOfMissingRequiredHeaderMupripart(
        string $partName,
        string $headerName,
        OperationAddress $address
    ): self {
        $exception          = static::fromAddr($address);
        $exception->message = sprintf('Missing required header "%s" for %s in multipart "%s"', $headerName, $address, $partName);

        return $exception;
    }

    public static function becauseValueDoesNotMatchSchema(string $headerName, string $headerValue, OperationAddress $address, SchemaMismatch $prev): self
    {
        $exception          = static::fromAddrAndPrev($address, $prev);
        $exception->message = sprintf('Value "%s" for header "%s" is invalid for %s', $headerValue, $headerName, $address);

        return $exception;
    }

    public static function becauseValueDoesNotMatchSchemaMultipart(
        string $partName,
        string $headerName,
        string $headerValue,
        OperationAddress $address,
        SchemaMismatch $prev
    ): self {
        $exception          = static::fromAddrAndPrev($address, $prev);
        $exception->message = sprintf('Value "%s" for header "%s" is invalid for "%s" in multipart "%s"', $headerValue, $headerName, $address, $partName);

        return $exception;
    }

    public static function becauseOfUnexpectedHeaderIsNotAllowed(string $header, OperationAddress $address): self
    {
        $exception          = static::fromAddr($address);
        $exception->message = sprintf('Header "%s" is not allowed for %s', $header, $address);

        return $exception;
    }

    public static function becauseContentTypeIsNotExpected(string $contentType, OperationAddress $addr): self
    {
        $exception          = static::fromAddr($addr);
        $exception->message = sprintf('Content-Type "%s" is not expected for %s', $contentType, $addr);

        return $exception;
    }
}
