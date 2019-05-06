<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use OpenAPIValidation\PSR7\PathAddress;
use PHPUnit\Framework\TestCase;

class PathAddressTest extends TestCase
{
    function dataProviderParse()
    {
        return [
            ["/users/{id}/group/{group}", "/users/12/group/admin", ['id' => 12, 'group' => 'admin']],
            ["/users/{id}", "/users/12", ['id' => 12]],
            ["/users/{id}/", "/users/12/", ['id' => 12]],
            ["/users/{id}/", "/users/22.5/", ['id' => 22.5]],
            ["/users/{id}/{name}", "/users/22/admin", ['id' => 22, 'name' => 'admin']],
        ];
    }

    /**
     * @dataProvider dataProviderParse
     */
    public function test_it_parses_params(string $spec, string $url, array $result)
    {
        $parsed = PathAddress::parseParams($spec, $url);

        $this->assertTrue($result === $parsed);
    }


    function dataProviderMatch()
    {
        return [
            ['/users/{id}', '/users/12', true],
            ['/users/{id}', '/users/word', true],
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
    public function test_it_matches_path_against_spec(string $spec, string $path, bool $result)
    {
        $this->assertEquals($result, PathAddress::isPathMatchesSpec($spec, $path));
    }


}
