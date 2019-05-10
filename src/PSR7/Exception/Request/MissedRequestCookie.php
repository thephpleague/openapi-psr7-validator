<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Request;

use OpenAPIValidation\PSR7\OperationAddress;
use RuntimeException;
use function sprintf;

class MissedRequestCookie extends RuntimeException
{
    /** @var string */
    protected $cookieName;
    /** @var OperationAddress */
    protected $addr;

    public static function fromOperationAddr(string $cookieName, OperationAddress $address) : self
    {
        $i = new self(
            sprintf(
                "Request does not contain cookie '%s' at [%s,%s]",
                $cookieName,
                $address->path(),
                $address->method()
            )
        );

        $i->cookieName = $cookieName;
        $i->addr       = $address;

        return $i;
    }

    public function cookieName() : string
    {
        return $this->cookieName;
    }

    public function addr() : OperationAddress
    {
        return $this->addr;
    }
}
