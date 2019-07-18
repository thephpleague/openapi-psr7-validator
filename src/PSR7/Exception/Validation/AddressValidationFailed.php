<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Validation;

use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\PSR7\OperationAddress;
use Throwable;
use function sprintf;

abstract class AddressValidationFailed extends ValidationFailed
{
    /** @var OperationAddress */
    private $address;

    /**
     * @return static
     */
    public static function fromAddrAndPrev(OperationAddress $address, ?Throwable $prev) : self
    {
        $ex          = new static(sprintf('Validation failed for %s', $address), $prev ? $prev->getCode() : 0, $prev);
        $ex->address = $address;

        return $ex;
    }

    /**
     * @return static
     */
    public static function fromAddr(OperationAddress $address) : self
    {
        $ex          = new static(sprintf('Validation failed for %s', $address));
        $ex->address = $address;

        return $ex;
    }

    public function getAddress() : OperationAddress
    {
        return $this->address;
    }
}
