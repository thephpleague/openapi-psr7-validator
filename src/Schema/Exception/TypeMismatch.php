<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Exception;

use function gettype;
use function sprintf;

// Validation for 'type' keyword failed against a given data
class TypeMismatch extends KeywordMismatch
{
    /**
     * @param mixed $value
     *
     * @return TypeMismatch
     */
    public static function becauseTypeDoesNotMatch(string $expected, $value): self
    {
        $exception          = new self(sprintf("Value expected to be '%s', '%s' given.", $expected, gettype($value)));
        $exception->data    =  $value;
        $exception->keyword = 'type';

        return $exception;
    }
}
