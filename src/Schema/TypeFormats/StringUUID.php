<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\TypeFormats;

use function preg_match;

class StringUUID
{
    public function __invoke(string $value) : bool
    {
        $patternUUIDV4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';

        return (bool) preg_match($patternUUIDV4, $value);
    }
}
