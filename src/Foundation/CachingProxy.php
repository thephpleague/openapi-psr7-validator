<?php

declare(strict_types=1);

namespace OpenAPIValidation\Foundation;

use cebe\openapi\spec\OpenApi;
use Psr\Cache\CacheItemPoolInterface;

// This class will protect expensive operation from being invoked by adding caching layer
class CachingProxy
{
    /**
     * Execute expensive operation if result is not in cache
     * If cache pool is null, avoid caching at all
     */
    public static function cachedRead(?CacheItemPoolInterface $cache, string $key, callable $expensiveOperation) : OpenApi
    {
        if (! $cache) {
            return $expensiveOperation();
        }

        $cachedSpec = $cache->getItem($key);

        if (! $cachedSpec->isHit()) {
            $cachedSpec->set($expensiveOperation());
            $cache->save($cachedSpec);
        }

        return $cachedSpec->get();
    }
}
