<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


use OpenAPIValidation\PSR7\ResponseAddress;

class UnexpectedResponseHeader extends \RuntimeException
{
    /** @var string */
    protected $headerName;
    /** @var ResponseAddress */
    protected $addr;

    static function fromResponseAddr(string $headerName, ResponseAddress $address): self
    {
        $i = new self(
            sprintf("Response header '%s' at [%s,%s,%d] has name which is not found in the spec",
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

    /**
     * @return string
     */
    public function headerName(): string
    {
        return $this->headerName;
    }

    /**
     * @return ResponseAddress
     */
    public function addr()
    {
        return $this->addr;
    }


}