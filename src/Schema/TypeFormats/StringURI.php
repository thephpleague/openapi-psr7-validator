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
            // namespace 'League\Uri' is provided by multiple packages, but PHPStan does not support merging them
            // @phpstan-ignore-next-line
            UriString::parse($value);

            return true;
        } catch (SyntaxError $error) {
            return false;
        }
    }
}
