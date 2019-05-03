<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


use OpenAPIValidation\Schema\Exception\FormatMismatch;

class NumberFloat
{
    function __invoke($value): void
    {
        if (!is_float($value + 0)) {
            throw FormatMismatch::fromFormat('float', $value);
        }
    }

}