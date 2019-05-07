<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception\Request;


use OpenAPIValidation\PSR7\OperationAddress;

class MissedRequestQueryArgument extends \RuntimeException
{
    /** @var string */
    protected $queryArgumentName;
    /** @var OperationAddress */
    protected $addr;

    static function fromOperationAddr(string $queryArgumentName, OperationAddress $address): self
    {
        $i = new self(
            sprintf("Request does not contain query argument '%s' at [%s,%s]",
                $queryArgumentName,
                $address->path(),
                $address->method()
            )
        );

        $i->queryArgumentName = $queryArgumentName;
        $i->addr              = $address;
        return $i;
    }

    /**
     * @return string
     */
    public function queryArgumentName(): string
    {
        return $this->queryArgumentName;
    }

    /**
     * @return OperationAddress
     */
    public function addr()
    {
        return $this->addr;
    }


}