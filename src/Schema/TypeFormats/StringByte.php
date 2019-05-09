<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


class StringByte
{
    function __invoke($value): bool
    {
        //base64-encoded characters, for example, U3dhZ2dlciByb2Nrcw==

        return base64_encode(base64_decode($value, true)) === $value;
    }
}