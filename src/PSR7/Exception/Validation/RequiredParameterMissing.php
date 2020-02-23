<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception\Validation;

use League\OpenAPIValidation\PSR7\OperationAddress;

class RequiredParameterMissing extends AddressValidationFailed
{
    /** @var string */
    protected $name;

    public static function fromNameAndAddr(string $name, OperationAddress $addr) : self
    {
        $exception       = static::fromAddr($addr);
        $exception->name = $name;

        return $exception;
    }

    public function name() : string
    {
        return $this->name;
    }
}
