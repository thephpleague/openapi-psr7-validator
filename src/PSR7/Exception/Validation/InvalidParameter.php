<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception\Validation;

use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;

class InvalidParameter extends AddressValidationFailed
{
    /** @var string */
    protected $name;

    /** @var mixed */
    protected $value;

    /**
     * @param mixed $value
     */
    public static function becauseValueDidNotMatchSchema(string $name, $value, OperationAddress $addr, SchemaMismatch $prev) : self
    {
        $exception        = static::fromAddrAndPrev($addr, $prev);
        $exception->name  = $name;
        $exception->value = $value;

        return $exception;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function value() : string
    {
        return (string) $this->value;
    }
}
