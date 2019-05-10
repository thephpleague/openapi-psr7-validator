<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\TypeFormats;

use function is_float;

class NumberFloat
{
    /**
     * @param mixed $value
     */
    public function __invoke($value) : bool
    {
        return is_float($value + 0);
    }
}
