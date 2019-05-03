<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


use OpenAPIValidation\Schema\Exception\FormatMismatch;

class StringByte
{
    function __invoke($value): void
    {
        //base64-encoded characters, for example, U3dhZ2dlciByb2Nrcw==

        if (base64_encode(base64_decode($value, true)) !== $value) {
            throw FormatMismatch::fromFormat('byte', $value);
        }
    }
}