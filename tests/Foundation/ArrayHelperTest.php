<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Foundation;

use Generator;
use League\OpenAPIValidation\Foundation\ArrayHelper;
use PHPUnit\Framework\TestCase;

use function count;
use function parse_str;

class ArrayHelperTest extends TestCase
{
    /**
     * @param array<int|string, mixed> $initArr
     * @param mixed                    $value
     * @param array<int|string, mixed> $expected
     *
     * @dataProvider getTestDataForSetRecursive
     */
    public function testSetRecursive(array $initArr, string $key, $value, array $expected): void
    {
        $arr = $initArr;

        ArrayHelper::setRecursive($arr, $key, $value);
        self::assertEquals($expected, $arr);

        if (count($initArr) !== 0) {
            return;
        }

        $queryString = "{$key}=$value";
        parse_str($queryString, $arr);

        self::assertEquals($expected, $arr);
    }

    public function getTestDataForSetRecursive(): Generator
    {
        yield 'set value by key' => [
            [],
            'item',
            1,
            ['item' => 1],
        ];

        yield 'add value to an empty non-existent list' => [
            [],
            'list[]',
            1,
            ['list' => [1]],
        ];

        yield 'add value to an empty existent list' => [
            ['list' => []],
            'list[]',
            1,
            ['list' => [1]],
        ];

        yield 'add value to a not empty list' => [
            ['list' => [0]],
            'list[]',
            1,
            ['list' => [0, 1]],
        ];

        yield 'override existing value, if it is not array' => [
            ['list' => 0],
            'list[]',
            1,
            ['list' => [1]],
        ];

        yield 'set values with string keys' => [
            [],
            'list[some_key][another_key]',
            1,
            ['list' => ['some_key' => ['another_key' => 1]]],
        ];

        yield 'set values to a list of objects' => [
            ['list' => [['another_key' => 0]]],
            'list[][another_key]',
            1,
            ['list' => [['another_key' => 0], ['another_key' => 1]]],
        ];

        yield 'somehow sets value on incorrect key #1' => [
            [],
            'list[asd]another_[key]',
            1,
            ['list' => ['asd' => 1]],
        ];

        yield 'somehow sets value on incorrect key #2' => [
            [],
            'list]asdanother_key',
            1,
            ['list]asdanother_key' => 1],
        ];

        yield 'somehow sets value on incorrect key #3' => [
            [],
            'list[asdanother_key',
            1,
            ['list_asdanother_key' => 1],
        ];

        yield 'somehow sets value on incorrect key #4' => [
            [],
            'list]asd[another_key',
            1,
            ['list]asd_another_key' => 1],
        ];
    }
}
