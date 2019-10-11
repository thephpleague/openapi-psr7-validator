<?php

declare(strict_types=1);

namespace OpenAPIValidation\Foundation;

use DateTime;
use DateTimeZone;
use function preg_match;
use function strpos;
use function strtoupper;

class Rfc3339
{
    public const RFC3339_PATTERN = '/^(\d{4}-\d{2}-\d{2}[T ]{1}\d{2}:\d{2}:\d{2})(\.\d+)?(Z|([+-]\d{2}):?(\d{2}))$/';

    public static function createFromString(string $string) : ?DateTime
    {
        if (! preg_match(self::RFC3339_PATTERN, strtoupper($string), $matches)) {
            return null;
        }

        $dateAndTime  = $matches[1];
        $microseconds = $matches[2] ?: '.000000';
        $timeZone     = $matches[3] !== 'Z' ? $matches[4] . ':' . $matches[5] : '+00:00';
        $dateFormat   = strpos($dateAndTime, 'T') === false ? 'Y-m-d H:i:s.uP' : 'Y-m-d\TH:i:s.uP';
        $dateTime     = DateTime::createFromFormat($dateFormat, $dateAndTime . $microseconds . $timeZone, new DateTimeZone('UTC'));

        return $dateTime ?: null;
    }
}
