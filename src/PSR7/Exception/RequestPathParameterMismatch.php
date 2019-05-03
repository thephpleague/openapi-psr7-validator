<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


use OpenAPIValidation\PSR7\OperationAddress;

class RequestPathParameterMismatch extends NoOperation
{
    /** @var string like "/users/admin" */
    protected $actualPath;

    static function fromAddrAndCauseException(OperationAddress $addr, string $actualPath, \Throwable $cause): self
    {
        $i = new self(
            sprintf(
                "OpenAPI spec at [%s,%s] does not match the path of the request '%s': %s",
                $addr->path(),
                $addr->method(),
                $actualPath,
                $cause->getMessage()
            ),
            0,
            $cause
        );

        $i->path       = $addr->path();
        $i->method     = $addr->method();
        $i->actualPath = $actualPath;

        return $i;
    }

    /**
     * @return string
     */
    public function actualPath(): string
    {
        return $this->actualPath;
    }
}