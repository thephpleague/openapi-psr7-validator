<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


class StringHostname
{
    function __invoke($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_DOMAIN) !== false;
    }
}