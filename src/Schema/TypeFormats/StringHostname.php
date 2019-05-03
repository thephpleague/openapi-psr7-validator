<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


use OpenAPIValidation\Schema\Exception\FormatMismatch;

class StringHostname
{
    function __invoke($value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_DOMAIN)) {
            throw FormatMismatch::fromFormat('hostname', $value);
        }
    }
}