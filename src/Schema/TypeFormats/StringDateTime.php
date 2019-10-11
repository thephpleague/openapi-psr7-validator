<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\TypeFormats;

use OpenAPIValidation\Foundation\Rfc3339;

class StringDateTime
{
    public function __invoke(string $value) : bool
    {
        // the date-time notation as defined by RFC 3339, section 5.6, for example, 2017-07-21T17:32:28Z

        return Rfc3339::createFromString($value) !== null;
    }
}
