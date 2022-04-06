<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Foundation;

use function array_keys;
use function count;
use function is_array;
use function preg_match;
use function range;
use function str_replace;

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

    /**
     * @param array<int|string, mixed> $arr
     * @param mixed                    $value
     */
    public static function setRecursive(array &$arr, string $key, $value): void
    {
        $_arr = &$arr;
        while (preg_match('#^([^\]]+)\[([^\]]*)\](.*)$#', $key, $matches)) {
            if (! isset($arr[$matches[1]]) || ! is_array($arr[$matches[1]])) {
                $_arr[$matches[1]] = [];
            }

            if ($matches[2] === '') {
                $key = count($_arr[$matches[1]]);
            } else {
                $key = $matches[2];
            }

            $key .= preg_match('#^\[.*\]$#', $matches[3]) ? $matches[3] : '';
            $_arr = &$_arr[$matches[1]];
        }

        $key        = str_replace('[', '_', $key);
        $_arr[$key] = $value;
    }
}
