<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\TypeFormats;

use function preg_match;

class StringUUID
{
    public function __invoke(string $value): bool
    {
        $pattern = '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/i';

        return (bool) preg_match($pattern, $value);
    }
}
