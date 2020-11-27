<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\TypeFormats;

use function base64_decode;
use function base64_encode;

class StringByte
{
    /**
     * @param mixed $value
     */
    public function __invoke($value): bool
    {
        //base64-encoded characters, for example, U3dhZ2dlciByb2Nrcw==

        return base64_encode(base64_decode($value, true)) === $value;
    }
}
