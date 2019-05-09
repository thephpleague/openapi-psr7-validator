<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


class StringDate
{
    function __invoke($value): bool
    {
        // full-date notation as defined by RFC 3339, section 5.6, for example, 2017-07-21

        return \DateTime::createFromFormat('Y-m-d', $value) !== false;
    }
}