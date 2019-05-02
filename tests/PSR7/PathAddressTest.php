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
    function dataProvider()
    {
        return [
            ["/users/{id}/group/{group}", "/users/12/group/admin", ['id' => 12, 'group' => 'admin']],
            ["/users/{id}", "/users/12", ['id' => 12]],
            ["/users/{id}/", "/users/12/", ['id' => 12]],
            ["/users/{id}/", "/users/22.5/", ['id' => 22.5]],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test_it_parses_params(string $spec, string $url, array $result)
    {
        $parsed = PathAddress::parseParams($spec, $url);

        $this->assertTrue($result === $parsed);
    }
}
