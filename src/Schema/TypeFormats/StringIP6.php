<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;


use OpenAPIValidation\Schema\Exception\FormatMismatch;

class StringIP6
{
    function __invoke($value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw FormatMismatch::fromFormat('ipv6', $value);
        }
    }
}