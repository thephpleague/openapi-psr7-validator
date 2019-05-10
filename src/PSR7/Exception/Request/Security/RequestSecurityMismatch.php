<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Request\Security;

use OpenAPIValidation\PSR7\OperationAddress;
use RuntimeException;
use Throwable;
use function sprintf;

class RequestSecurityMismatch extends RuntimeException
{
    /** @var OperationAddress */
    protected $addr;

    public static function fromOperationAddr(OperationAddress $address, ?Throwable $prev = null) : self
    {
        $i = new self(
            sprintf(
                'Request does not match security schemes [%s,%s]',
                $address->path(),
                $address->method()
            ),
            0,
            $prev
        );

        $i->addr = $address;

        return $i;
    }

    public function addr() : OperationAddress
    {
        return $this->addr;
    }
}
