<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\TypeFormats;

use OpenAPIValidation\Foundation\Rfc3339DatetimeFactory;

class StringDateTime
{
    public function __invoke(string $value) : bool
    {
        return Rfc3339DatetimeFactory::createFromString($value) !== null;
    }
}
