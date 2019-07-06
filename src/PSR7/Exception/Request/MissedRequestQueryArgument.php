<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Request;

use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\PSR7\OperationAddress;
use function sprintf;

class MissedRequestQueryArgument extends ValidationFailed
{
    /** @var string */
    protected $queryArgumentName;
    /** @var OperationAddress */
    protected $addr;

    public static function fromOperationAddr(string $queryArgumentName, OperationAddress $address) : self
    {
        $i = new self(
            sprintf(
                "Request does not contain query argument '%s' at [%s,%s]",
                $queryArgumentName,
                $address->path(),
                $address->method()
            )
        );

        $i->queryArgumentName = $queryArgumentName;
        $i->addr              = $address;

        return $i;
    }

    public function queryArgumentName() : string
    {
        return $this->queryArgumentName;
    }

    public function addr() : OperationAddress
    {
        return $this->addr;
    }
}
