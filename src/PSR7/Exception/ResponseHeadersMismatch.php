<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


use OpenAPIValidation\PSR7\ResponseAddress;

class ResponseHeadersMismatch extends NoResponseCode
{
    static function fromAddrAndCauseException(ResponseAddress $addr, \Throwable $cause): self
    {
        $i = new self(
            sprintf(
                "OpenAPI spec does not match the headers of the response [%s,%s,%d]: %s",
                $addr->path(),
                $addr->method(),
                $addr->responseCode(),
                $cause->getMessage()
            ),
            0,
            $cause
        );

        $i->path         = $addr->path();
        $i->method       = $addr->method();
        $i->responseCode = $addr->responseCode();

        return $i;
    }

}