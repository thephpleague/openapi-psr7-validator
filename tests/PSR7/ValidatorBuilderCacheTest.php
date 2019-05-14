<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use Cache\Adapter\PHPArray\ArrayCachePool;
use OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use function copy;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;

final class ValidatorBuilderCacheTest extends TestCase
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
        $v1 = (new ValidatorBuilder())->fromYamlFile($oasFile)->setCache($cache)->getServiceRequestValidator();

        // drop oas file contents and read again
        file_put_contents($oasFile, 'rubbish');
        $v2 = (new ValidatorBuilder())->fromYamlFile($oasFile)->setCache($cache)->getServiceRequestValidator();

        self::assertEquals($v1, $v2);
    }

    public function testItUtilizesCacheKeyOverride() : void
    {
        // configure cache
        $pool  = [];
        $cache = new ArrayCachePool(10, $pool);

        $specFile = __DIR__ . '/../stubs/uber.yaml';

        // parse file
        $cacheKey = 'custom_key';
        (new ValidatorBuilder())->fromYamlFile($specFile)
            ->setCache($cache)
            ->overrideCacheKey($cacheKey)
            ->getServiceRequestValidator();

        self::assertTrue($cache->getItem($cacheKey)->isHit());
    }
}
