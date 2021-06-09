<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception\Validation;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use Throwable;

use function sprintf;

abstract class AddressValidationFailed extends ValidationFailed
{
    /** @var OperationAddress */
    private $address;

    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return static
     */
    public static function fromAddrAndPrev(OperationAddress $address, Throwable $prev): self
    {
        $ex          = new static(sprintf('Validation failed for %s', $address), $prev->getCode(), $prev);
        $ex->address = $address;

        return $ex;
    }

    /**
     * @return static
     */
    public static function fromAddr(OperationAddress $address): self
    {
        $ex          = new static(sprintf('Validation failed for %s', $address));
        $ex->address = $address;

        return $ex;
    }

    public function getAddress(): OperationAddress
    {
        return $this->address;
    }
}
