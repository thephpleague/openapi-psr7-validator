<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Exception;

use function gettype;
use function sprintf;

class TypeMismatch extends ValidationKeywordFailed
{
    /**
     * @param mixed $value
     *
     * @return TypeMismatch
     */
    public static function becauseTypeDoesNotMatch(string $expected, $value) : self
    {
        $exception          = new self(sprintf("Value expected to be '%s', '%s' given.", $expected, gettype($value)));
        $exception->data    =  $value;
        $exception->keyword = 'type';

        return $exception;
    }
}
