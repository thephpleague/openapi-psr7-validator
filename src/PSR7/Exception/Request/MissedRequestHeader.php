<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception\Request;


use OpenAPIValidation\PSR7\OperationAddress;

class MissedRequestHeader extends \RuntimeException
{
    /** @var string */
    protected $headerName;
    /** @var OperationAddress */
    protected $addr;

    static function fromOperationAddr(string $headerName, OperationAddress $address, \Throwable $prev = null): self
    {
        $i = new self(
            sprintf("Request header '%s' at [%s,%s] not found",
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

    /**
     * @return string
     */
    public function headerName(): string
    {
        return $this->headerName;
    }

    /**
     * @return OperationAddress
     */
    public function addr()
    {
        return $this->addr;
    }


}