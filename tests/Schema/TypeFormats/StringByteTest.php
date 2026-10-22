<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\TypeFormats;

use League\OpenAPIValidation\Schema\TypeFormats\StringByte;
use PHPUnit\Framework\TestCase;

class StringByteTest extends TestCase
{
    /**
     * @return array<array<string>>
     */
    public function greenByteDataProvider(): array
    {
        return [
            'padded' => ['U3dhZ2dlciByb2Nrcw=='],
            'empty string' => [''],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function redByteDataProvider(): array
    {
        return [
            'invalid characters' => ['%%%'],
            'whitespace' => ['U3dh Z2dlcg=='],
            'misplaced padding' => ['U3=dhZ2dlcg='],
        ];
    }

    /**
     * @dataProvider greenByteDataProvider
     */
    public function testGreenByteFormat(string $value): void
    {
        $this->assertTrue((new StringByte())($value));
    }

    /**
     * @dataProvider redByteDataProvider
     */
    public function testRedByteFormat(string $value): void
    {
        $this->assertFalse((new StringByte())($value));
    }
}
