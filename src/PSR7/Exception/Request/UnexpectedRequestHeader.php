<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Request;

use OpenAPIValidation\PSR7\OperationAddress;
use RuntimeException;
use Throwable;
use function sprintf;

class UnexpectedRequestHeader extends RuntimeException
{
    /** @var string */
    protected $headerName;
    /** @var OperationAddress */
    protected $addr;

    public static function fromOperationAddr(string $headerName, OperationAddress $address, ?Throwable $prev = null) : self
    {
        $i = new self(
            sprintf(
                "Request header '%s' at [%s,%s] has name which is not found in the spec",
                $headerName,
                $address->path(),
                $address->method()
            ),
            0,
            $prev
        );

        $i->headerName = $headerName;
        $i->addr       = $address;

        return $i;
    }

    public function headerName() : string
    {
        return $this->headerName;
    }

    public function addr() : OperationAddress
    {
        return $this->addr;
    }
}
