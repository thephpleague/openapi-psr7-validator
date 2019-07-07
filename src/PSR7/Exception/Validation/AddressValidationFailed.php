<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception\Validation;

use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\PSR7\OperationAddress;
use Throwable;
use function sprintf;

class AddressValidationFailed extends ValidationFailed
{
    /** @var OperationAddress */
    private $address;

    public function __construct(OperationAddress $address, ?Throwable $prev = null)
    {
        parent::__construct(sprintf('Validation failed for %s', $address), $prev ? $prev->getCode() : null, $prev);
        $this->address = $address;
    }

    public function getAddress() : OperationAddress
    {
        return $this->address;
    }
}
