<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


use OpenAPIValidation\Schema\Exception\FormatMismatch;

class StringDateTime implements Format
{

    public function validate($value): void
    {
        // the date-time notation as defined by RFC 3339, section 5.6, for example, 2017-07-21T17:32:28Z

        if (\DateTime::createFromFormat(\DateTime::RFC3339, $value) === false) {
            throw FormatMismatch::fromFormat('date-time', $value);
        }
    }
}