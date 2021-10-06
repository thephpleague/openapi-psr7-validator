<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\TypeFormats;

use DateTime;

class StringDate
{
    /**
     * @param mixed $value
     */
    public function __invoke($value): bool
    {
        // full-date notation as defined by RFC 3339, section 5.6, for example, 2017-07-21

        $datetime = DateTime::createFromFormat('Y-m-d', $value);
        if ($datetime === false) {
            return false;
        }

        return $datetime->format('Y-m-d') === $value;
    }
}
