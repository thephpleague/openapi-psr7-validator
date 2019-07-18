<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Validation;

use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use function sprintf;

class InvalidBody extends AddressValidationFailed
{
    public static function becauseBodyDoesNotMatchSchema(
        string $contentType,
        OperationAddress $addr,
        SchemaMismatch $prev
    ) : self {
        $exception          = static::fromAddrAndPrev($addr, $prev);
        $exception->message = sprintf('Body does not match schema for content-type "%s" for %s', $contentType, $addr);

        return $exception;
    }

    public static function becauseBodyDoesNotMatchSchemaMultipart(
        string $partName,
        string $contentType,
        OperationAddress $addr,
        ?SchemaMismatch $prev = null
    ) : self {
        $exception          = static::fromAddrAndPrev($addr, $prev);
        $exception->message = sprintf(
            'Multipart body does not match schema for part "%s" with content-type "%s" for %s',
            $partName,
            $contentType,
            $addr
        );

        return $exception;
    }

    public static function becauseBodyIsNotValidJson(string $error, OperationAddress $addr) : self
    {
        $exception          = static::fromAddr($addr);
        $exception->message = sprintf('JSON parsing failed with "%s" for %s', $error, $addr);

        return $exception;
    }
}
