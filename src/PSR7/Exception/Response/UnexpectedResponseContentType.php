<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception\Response;


use OpenAPIValidation\PSR7\ResponseAddress;

class UnexpectedResponseContentType extends \RuntimeException
{
    /** @var string */
    protected $contentType;
    /** @var ResponseAddress */
    protected $addr;

    static function fromResponseAddr(string $contentType, ResponseAddress $address, \Throwable $prev = null): self
    {
        $i = new self(
            sprintf("Response body at [%s,%s,%d] has Content-Type %s, which is not found in the spec",
                $address->path(),
                $address->method(),
                $address->responseCode(),
                $contentType
            ),
            0,
            $prev
        );

        $i->contentType = $contentType;
        $i->addr        = $address;
        return $i;
    }

    /**
     * @return string
     */
    public function contentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return ResponseAddress
     */
    public function addr()
    {
        return $this->addr;
    }


}