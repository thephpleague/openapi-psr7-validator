<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\TypeFormats;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

class StringEmail
{
    public function __invoke(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
