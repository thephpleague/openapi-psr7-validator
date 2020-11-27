<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\CacheableSchemaFactory;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @covers \League\OpenAPIValidation\PSR7\ValidatorBuilder
 */
final class ValidatorBuilderTest extends TestCase
{
    public function testItCachesParsedOpenApiSpec(): void
    {
        $cache = new ArrayAdapter(0, true, 0, 10);

        $factory = $this->createMock(CacheableSchemaFactory::class);
        $factory->expects($this->once())->method('createSchema')
            ->willReturn(new OpenApi([]));
        $cacheKey = 'the_cache_key';
        $factory->expects($this->exactly(2))->method('getCacheKey')
            ->willReturn($cacheKey);

        $v1 = (new ValidatorBuilder())->setSchemaFactory($factory)->setCache($cache)->getServerRequestValidator();
        $v2 = (new ValidatorBuilder())->setSchemaFactory($factory)->setCache($cache)->getServerRequestValidator();

        self::assertEquals($v1, $v2);
        self::assertTrue($cache->getItem($cacheKey)->isHit());
    }

    public function testItUtilizesCacheKeyOverride(): void
    {
        $cache = new ArrayAdapter(0, true, 0, 10);

        $factory = $this->createMock(CacheableSchemaFactory::class);
        $factory->expects($this->once())->method('createSchema')
            ->willReturn(new OpenApi([]));
        $factory->expects($this->never())->method('getCacheKey');

        // parse file
        $cacheKey = 'custom_key';
        (new ValidatorBuilder())->setSchemaFactory($factory)
            ->setCache($cache)
            ->overrideCacheKey($cacheKey)
            ->getServerRequestValidator();

        self::assertTrue($cache->getItem($cacheKey)->isHit());
    }
}
