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

        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        return base64_encode($decoded) === $value;
    }
}
