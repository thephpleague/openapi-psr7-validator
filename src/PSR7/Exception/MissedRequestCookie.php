<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


use OpenAPIValidation\PSR7\OperationAddress;

class MissedRequestCookie extends \RuntimeException
{
    /** @var string */
    protected $cookieName;
    /** @var OperationAddress */
    protected $addr;

    static function fromOperationAddr(string $cookieName, OperationAddress $address): self
    {
        $i = new self(
            sprintf("Request does not contain cookie '%s' at [%s,%s]",
                $cookieName,
                $address->path(),
                $address->method()
            )
        );

        $i->cookieName = $cookieName;
        $i->addr       = $address;
        return $i;
    }

    /**
     * @return string
     */
    public function cookieName(): string
    {
        return $this->cookieName;
    }

    /**
     * @return OperationAddress
     */
    public function addr()
    {
        return $this->addr;
    }


}