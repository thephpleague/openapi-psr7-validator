<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Request;

use OpenAPIValidation\PSR7\OperationAddress;
use RuntimeException;
use function sprintf;

class UnexpectedRequestContentType extends RuntimeException
{
    /** @var string */
    protected $contentType;
    /** @var OperationAddress */
    protected $addr;

    public static function fromAddr(string $contentType, OperationAddress $address) : self
    {
        $i = new self(
            sprintf('Request body at [%s,%s] has Content-Type %s, which is not found in the spec', $address->path(), $address->method(), $contentType)
        );

        $i->contentType = $contentType;
        $i->addr        = $address;

        return $i;
    }

    public function contentType() : string
    {
        return $this->contentType;
    }

    public function addr() : OperationAddress
    {
        return $this->addr;
    }
}
