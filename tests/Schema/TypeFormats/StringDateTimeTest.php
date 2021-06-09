<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\TypeFormats;

use League\OpenAPIValidation\Schema\TypeFormats\StringDateTime;
use PHPUnit\Framework\TestCase;

final class StringDateTimeTest extends TestCase
{
    /**
     * @dataProvider dateTimeGreenDataProvider
     */
    public function testGreenDateTimeTypeFormat(string $dateTime): void
    {
        $this->assertTrue((new StringDateTime())($dateTime));
    }

    /**
     * @return string[][]
     */
    public function dateTimeGreenDataProvider(): array
    {
        return [
            ['1985-04-12T23:20:50.52Z'],
            ['1937-01-01 12:00:27Z'],
            ['1937-01-01 12:00:27+00:20'],
            ['1937-01-01 12:00:27.666666+00:20'],
            ['1937-01-01T12:00:27.87+00:20'],
            ['1996-12-19T16:39:57-08:00'],
            ['1990-12-31T23:59:60Z'],
            ['1990-12-31T15:59:60-08:00'],
        ];
    }

    /**
     * @dataProvider dateTimeRedDataProvider
     */
    public function testRedDateTimeTypeFormat(string $dateTime): void
    {
        $this->assertFalse((new StringDateTime())($dateTime));
    }

    /**
     * @return string[][]
     */
    public function dateTimeRedDataProvider(): array
    {
        return [
            ['1985-04-12'],
            ['1985-04-12 23:12:12'],
            ['1985-04-12T23:12'],
            ['1985-04-12T23:20:50.52'],
            [''],
            ['somestring'],
        ];
    }
}
