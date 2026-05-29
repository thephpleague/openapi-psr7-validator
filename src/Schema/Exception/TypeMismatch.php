<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Exception;

use League\OpenAPIValidation\Foundation\ArrayHelper;

use function gettype;
use function implode;
use function is_array;
use function sprintf;

// Validation for 'type' keyword failed against a given data
class TypeMismatch extends KeywordMismatch
{
    /**
     * @param string[] $expected
     * @param mixed    $value
     *
     * @return TypeMismatch
     */
    public static function becauseTypeDoesNotMatch(array $expected, $value): self
    {
        $givenType          = is_array($value) && ArrayHelper::isAssoc($value) ? 'object' : gettype($value);
        $exception          = new self(sprintf("Value expected to be '%s', but '%s' given.", implode(', ', $expected), $givenType));
        $exception->data    = $value;
        $exception->keyword = 'type';

        return $exception;
    }
}
