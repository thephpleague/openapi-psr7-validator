<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\TypeFormats;

use function preg_match;

class StringUUID
{
    public function __invoke(string $value) : bool
    {
        $patternUUIDV4 = '/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/';

        return (bool) preg_match($patternUUIDV4, $value);
    }
}
