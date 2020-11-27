<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\TypeFormats;

use DateTime;

class StringDateTime
{
    /**
     * RFC 3339, section 5.6 DateTime format
     * for example, 2017-07-21T17:32:28Z or with optional fractional seconds, 2017-07-21T17:32:28.123Z.
     *
     * @var array<string>
     */
    private static $dateTimeFormats = ['Y-m-d\TH:i:sP', 'Y-m-d H:i:sP', 'Y-m-d\TH:i:s.uP', 'Y-m-d H:i:s.uP'];

    public function __invoke(string $value): bool
    {
        foreach (self::$dateTimeFormats as $dateTimeFormat) {
            if (DateTime::createFromFormat($dateTimeFormat, $value) !== false) {
                return true;
            }
        }

        return false;
    }
}
