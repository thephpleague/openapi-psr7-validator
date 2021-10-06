<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\TypeFormats;

use League\OpenAPIValidation\Schema\TypeFormats\StringDate;
use PHPUnit\Framework\TestCase;

final class StringDateTest extends TestCase
{
    /**
     * @dataProvider dateGreenDataProvider
     */
    public function testGreendateTypeFormat(string $date): void
    {
        $this->assertTrue((new StringDate())($date));
    }

    /**
     * @return string[][]
     */
    public function dateGreenDataProvider(): array
    {
        return [
            ['1985-04-12'],
            ['1937-01-01'],
            ['1996-12-19'],
            ['1990-12-31'],
        ];
    }

    /**
     * @dataProvider dateRedDataProvider
     */
    public function testRedDateTypeFormat(string $date): void
    {
        $this->assertFalse((new StringDate())($date));
    }

    /**
     * @return string[][]
     */
    public function dateRedDataProvider(): array
    {
        return [
            ['2021-0-32'],
            ['2021-09-32'],
            ['0000-00-00'],
            [''],
            ['somestring'],
        ];
    }
}
