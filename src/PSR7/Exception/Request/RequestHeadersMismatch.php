<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Request;

use OpenAPIValidation\PSR7\Exception\NoOperation;
use OpenAPIValidation\PSR7\OperationAddress;
use Throwable;
use function sprintf;

class RequestHeadersMismatch extends NoOperation
{
    public static function fromAddrAndCauseException(OperationAddress $addr, Throwable $cause) : self
    {
        $i = new self(
            sprintf(
                'OpenAPI spec does not match the headers of the request [%s,%s]: %s',
                $addr->path(),
                $addr->method(),
                $cause->getMessage()
            ),
            0,
            $cause
        );

        $i->path   = $addr->path();
        $i->method = $addr->method();

        return $i;
    }
}
