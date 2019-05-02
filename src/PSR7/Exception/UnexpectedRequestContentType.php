<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


use OpenAPIValidation\PSR7\OperationAddress;

class UnexpectedRequestContentType extends \RuntimeException
{
    /** @var string */
    protected $contentType;
    /** @var OperationAddress */
    protected $addr;

    static function fromAddr(string $contentType, OperationAddress $address): self
    {
        $i = new self(
            sprintf("Response body at [%s,%s] has Content-Type %s, which is not found in the spec", $address->path(), $address->method(), $contentType)
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
     * @return OperationAddress
     */
    public function addr(): OperationAddress
    {
        return $this->addr;
    }


}