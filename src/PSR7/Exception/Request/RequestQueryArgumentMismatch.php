<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception\Request;


use OpenAPIValidation\PSR7\Exception\NoOperation;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;

class RequestQueryArgumentMismatch extends NoOperation
{
    static function fromAddrAndCauseException(OperationAddress $addr, \Throwable $cause): self
    {
        $i = new self(
            sprintf(
                "OpenAPI spec does not match the query argument '%s' of the request [%s,%s]: %s",
                $cause instanceof ValidationKeywordFailed ? implode("->",$cause->dataBreadCrumb()->buildChain()) : "?",
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