<?php
/**
 * Check if array has non-numeric keys
 *
 * @param array $arr
 * @return bool
 */
function isAssoc(array $arr): bool
{
    if ([] === $arr) return false;
    return array_keys($arr) === range(0, count($arr) - 1);
}
