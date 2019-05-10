<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Response;

use OpenAPIValidation\PSR7\ResponseAddress;
use RuntimeException;
use function sprintf;

class UnexpectedResponseHeader extends RuntimeException
{
    /** @var string */
    protected $headerName;
    /** @var ResponseAddress */
    protected $addr;

    public static function fromResponseAddr(string $headerName, ResponseAddress $address) : self
    {
        $i = new self(
            sprintf(
                "Response header '%s' at [%s,%s,%d] has name which is not found in the spec",
                $headerName,
                $address->path(),
                $address->method(),
                $address->responseCode()
            )
        );

        $i->headerName = $headerName;
        $i->addr       = $address;

        return $i;
    }

    public function headerName() : string
    {
        return $this->headerName;
    }

    public function addr() : ResponseAddress
    {
        return $this->addr;
    }
}
