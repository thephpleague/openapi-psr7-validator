<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Request;

use OpenAPIValidation\PSR7\Exception\NoOperation;
use OpenAPIValidation\PSR7\OperationAddress;
use Throwable;
use function sprintf;

class RequestPathParameterMismatch extends NoOperation
{
    /** @var string like "/users/admin" */
    protected $actualPath;

    public static function fromAddrAndCauseException(OperationAddress $addr, string $actualPath, Throwable $cause) : self
    {
        $i = new self(
            sprintf(
                "OpenAPI spec at [%s,%s] does not match the path of the request '%s': %s",
                $addr->path(),
                $addr->method(),
                $actualPath,
                $cause->getMessage()
            ),
            0,
            $cause
        );

        $i->path       = $addr->path();
        $i->method     = $addr->method();
        $i->actualPath = $actualPath;

        return $i;
    }

    public function actualPath() : string
    {
        return $this->actualPath;
    }
}
