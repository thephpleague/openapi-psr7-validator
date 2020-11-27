<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\TypeFormats;

use League\Uri\Exceptions\SyntaxError;
use League\Uri\UriString;

class StringURI
{
    public function __invoke(string $value): bool
    {
        try {
            UriString::parse($value);

            return true;
        } catch (SyntaxError $error) {
            return false;
        }
    }
}
