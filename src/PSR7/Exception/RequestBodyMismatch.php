<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


use OpenAPIValidation\PSR7\OperationAddress;

class RequestBodyMismatch extends NoOperation
{
    static function fromAddrAndCauseException(OperationAddress $addr, \Throwable $cause): self
    {
        $i = new self(
            sprintf(
                "OpenAPI spec does not match the body of the request [%s,%s]: %s",
                $addr->path(),
                $addr->method(),
                $cause->getMessage()
            ),
            0,
            $cause
        );

        $i->path   = $addr->path();
        $i->method = $addr->method();

        return $i;
    }

}