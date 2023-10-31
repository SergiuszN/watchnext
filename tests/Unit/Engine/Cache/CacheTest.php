<?php

namespace Unit\Engine\Cache;

use PHPUnit\Framework\TestCase;
use WatchNext\Engine\Cache\ApcuCache;
use WatchNext\Engine\Cache\CacheInterface;
use WatchNext\Engine\Cache\MemcachedCache;

class CacheTest extends TestCase
{
    public static function availableCacheManagerDataProvider(): array
    {
        return [
            'memcached' => [MemcachedCache::class],
            'apcu' => [ApcuCache::class],
        ];
    }

    /**
     * @dataProvider availableCacheManagerDataProvider
     */
    public function testCache(string $cacheClass): void
    {
        /** @var CacheInterface $cache */
        $cache = new $cacheClass();
        $someKey = 'some-key';
        $someData = 'some-data';

        $cache->clearAll();

        self::assertFalse($cache->has($someKey));
        $someCachedData = $cache->get($someKey, fn () => $someData);

        self::assertTrue($cache->has($someKey));
        self::assertEquals($someData, $someCachedData);
        self::assertEquals($someData, $cache->read($someKey));

        $someCachedData = $cache->get($someKey, fn () => 'other-data');
        self::assertEquals($someData, $someCachedData);

        $cache->delete($someKey);
        self::assertFalse($cache->has($someKey));
        $cache->set($someKey, $someData);
        self::assertTrue($cache->has($someKey));
        self::assertEquals($someData, $cache->read($someKey));

        $cache->clearAll();
        self::assertFalse($cache->has($someKey));
    }
}
