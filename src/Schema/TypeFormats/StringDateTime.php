<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\TypeFormats;

use DateTime;

class StringDateTime
{
    public function __invoke(string $value) : bool
    {
        // the date-time notation as defined by RFC 3339, section 5.6, for example, 2017-07-21T17:32:28Z

        return DateTime::createFromFormat(DateTime::RFC3339, $value) !== false;
    }
}
