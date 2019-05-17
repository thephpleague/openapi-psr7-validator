<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use OpenAPIValidation\PSR7\PathAddress;
use PHPUnit\Framework\TestCase;

final class PathAddressTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public function dataProviderParse() : array
    {
        return [
            ['/users/{id}/group/{group}', '/users/12/group/admin?a=2', ['id' => 12, 'group' => 'admin']],
            ['/users/{id}', '/users/12?', ['id' => 12]],
            ['/users/{id}/', '/users/12/', ['id' => 12]],
            ['/users/{id}/', '/users/22.5/', ['id' => 22.5]],
            ['/users/{id}/{name}', '/users/22/admin', ['id' => 22, 'name' => 'admin']],
        ];
    }

    /**
     * @param mixed[] $result
     *
     * @dataProvider dataProviderParse
     */
    public function testItParsesParams(string $spec, string $url, array $result) : void
    {
        $parsed = PathAddress::parseParams($spec, $url);

        $this->assertTrue($result === $parsed);
    }

    /**
     * @return mixed[][]
     */
    public function dataProviderMatch() : array
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
    public function testItMatchesPathAgainstSpec(string $spec, string $path, bool $result) : void
    {
        $this->assertEquals($result, PathAddress::isPathMatchesSpec($spec, $path));
    }
}
