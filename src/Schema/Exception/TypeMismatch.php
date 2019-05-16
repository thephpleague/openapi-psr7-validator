<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Exception;

use Exception;
use function gettype;
use function sprintf;

class TypeMismatch extends Exception
{
    /**
     * @param mixed $value
     *
     * @return TypeMismatch
     */
    public static function becauseTypeDoesNotMatch(string $expected, $value) : self
    {
        return new self(sprintf("Value expected to be '%s', '%s' given.", $expected, gettype($value)));
    }

    public static function becauseTypeIsNotKnown(string $type) : self
    {
        return new self("Type '%s' is unexpected.", $type);
    }
}
