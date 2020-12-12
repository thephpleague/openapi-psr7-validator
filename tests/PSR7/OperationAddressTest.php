<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidPath;
use League\OpenAPIValidation\PSR7\OperationAddress;
use PHPUnit\Framework\TestCase;

final class OperationAddressTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public function dataProviderParseGreen(): array
    {
        return [
            ['/users/{id}/group/{group}', '/users/12/group/admin?a=2', ['id' => '12', 'group' => 'admin']],
            ['/users/{id}', '/users/12?', ['id' => '12']],
            ['/users/{id}/', '/users/12/', ['id' => '12']],
            ['/users/{id}/', '/users/22.5/', ['id' => '22.5']],
            ['/users/{id}/{name}', '/users/22/admin', ['id' => '22', 'name' => 'admin']],
        ];
    }

    /**
     * @param mixed[] $result
     *
     * @dataProvider dataProviderParseGreen
     */
    public function testItParsesParams(string $spec, string $url, array $result): void
    {
        $addr = new OperationAddress($spec, 'post');

        $parsed = $addr->parseParams($url);

        $this->assertSame($result, $parsed);
    }

    /**
     * @return mixed[][]
     */
    public function dataProviderParseRed(): array
    {
        return [
            ['/users/{id}/', '/users/'],
            ['/users/{id}/action/{action}', '/users/10/action/'],
        ];
    }

    /**
     * @dataProvider dataProviderParseRed
     */
    public function testItThrowsIfParsingNotPossible(string $spec, string $url): void
    {
        $this->expectException(InvalidPath::class);
        $addr   = new OperationAddress($spec, 'post');
        $parsed = $addr->parseParams($url);
    }

    /**
     * @return mixed[][]
     */
    public function dataProviderMatch(): array
    {
        return [
            ['/users/{id}', '/users/12?data=extended', true],
            ['/users/{id}', '/users/word?', true],
            ['/users/{id}', '/users/word/', true],
            ['/users/{id}', '/users/', false],
            ['/users/{id}', '/users', false],
            ['/users/{id}', '/users/word/some', false],
            ['/users/{id}/group/{group}', '/users/12/group/admin', true],
            ['/users/{id}/group/{group}', '/users/word/group/admin', true],
            ['/users/{id}/group/{group}', '/users/word/group/', false],
            ['/users/{id}/{group}', '/users/word1/word2/', true],
        ];
    }

    /**
     * @dataProvider dataProviderMatch
     */
    public function testItMatchesPathAgainstSpec(string $spec, string $path, bool $result): void
    {
        $this->assertEquals($result, OperationAddress::isPathMatchesSpec($spec, $path));
    }
}
