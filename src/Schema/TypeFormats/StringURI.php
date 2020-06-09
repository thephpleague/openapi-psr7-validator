<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\TypeFormats;

use const FILTER_VALIDATE_URL;
use function filter_var;

class StringURI
{
    public function __invoke(string $value) : bool
    {
        if ($value === 'about:blank') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
