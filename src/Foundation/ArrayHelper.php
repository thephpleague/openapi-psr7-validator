<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Foundation;

use function array_keys;
use function count;
use function range;

final class ArrayHelper
{
    /**
     * Check if array has non-numeric keys
     *
     * JSON's objects and arrays are caster to PHP arrays.
     * To distinguish the two it evaluates keys of PHP array:
     * - if there are only numeric keys (0...N) then it returns true
     * - otherwise, if there are string keys it returns false
     *
     * @param mixed[] $arr
     */
    public static function isAssoc(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
