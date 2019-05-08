<?php

namespace OpenAPIValidation\Schema\Utils;

final class ArrayHelper
{
    public static function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
