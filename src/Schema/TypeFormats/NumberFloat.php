<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\TypeFormats;

use function is_float;
use function is_int;

class NumberFloat
{
    /**
     * @param mixed $value
     */
    public function __invoke($value): bool
    {
        // treat integers as valid floats
        return is_float($value + 0) || is_int($value + 0);
    }
}
