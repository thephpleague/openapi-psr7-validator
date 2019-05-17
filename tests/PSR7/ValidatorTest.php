<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use Cache\Adapter\PHPArray\ArrayCachePool;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use PHPUnit\Framework\TestCase;
use function copy;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;

final class ValidatorTest extends TestCase
{
    public function testItCachesParsedOpenapiSpecGreen() : void
    {
        // configure cache
        $pool  = [];
        $cache = new ArrayCachePool(10, $pool);

        // prepare tmp file with OAS
        $oasFile = tempnam(sys_get_temp_dir(), 'openapi_test_');
        copy(__DIR__ . '/../stubs/uber.yaml', $oasFile);

        // parse file
        $v1 = ServerRequestValidator::fromYamlFile($oasFile, $cache);

        // drop oas file contents and read again
        file_put_contents($oasFile, 'rubbish');
        $v2 = ServerRequestValidator::fromYamlFile($oasFile, $cache);

        self::assertEquals($v1, $v2);
    }
}
