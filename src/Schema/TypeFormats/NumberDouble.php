<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


class NumberDouble
{
    function __invoke($value): bool
    {
        return is_float($value + 0);
    }
}