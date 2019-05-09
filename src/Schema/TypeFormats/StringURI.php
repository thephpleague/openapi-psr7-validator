<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


use OpenAPIValidation\Schema\Exception\FormatMismatch;

class StringURI
{
    function __invoke($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}