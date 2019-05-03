<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


use OpenAPIValidation\Schema\Exception\FormatMismatch;

class StringDate
{
    function __invoke($value): void
    {
        // full-date notation as defined by RFC 3339, section 5.6, for example, 2017-07-21

        if (\DateTime::createFromFormat('Y-m-d', $value) === false) {
            throw FormatMismatch::fromFormat('date', $value);
        }
    }
}