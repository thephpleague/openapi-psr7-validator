<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\TypeFormats;

use League\OpenAPIValidation\Schema\TypeFormats\StringURI;
use PHPUnit\Framework\TestCase;

class StringURITest extends TestCase
{
    /**
     * @return array<array<string>>
     */
    public function greenURIDataProvider() : array
    {
        return [
            ['http://example.com'],
            ['about:blank'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function redURIDataProvider() : array
    {
        return [
            ['example.com'],
            ['www.example.com'],
        ];
    }

    /**
     * @dataProvider greenURIDataProvider
     */
    public function testGreenURIFormat(string $uri) : void
    {
        $this->assertTrue((new StringURI())($uri));
    }

    /**
     * @dataProvider redURIDataProvider
     */
    public function testRedURIFormat(string $uri) : void
    {
        $this->assertFalse((new StringURI())($uri));
    }
}
