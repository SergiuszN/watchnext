<?php

namespace Unit\Engine;

use PHPUnit\Framework\TestCase;
use WatchNext\Engine\Cache\FileSystemCache;
use WatchNext\Engine\Cache\MemcachedCache;
use WatchNext\Engine\Cache\VarDirectory;

class CacheTest extends TestCase {
    public static function availableCacheManagerDataProvider(): array {
        return [
            'memcached' => [MemcachedCache::class],
            'filesystem' => [FileSystemCache::class],
        ];
    }

    public function testVarDirectory(): void {
        $varPath = realpath(__DIR__ . '/../../../var');
        system("rm -rf $varPath");

        self::assertFalse(file_exists($varPath));

        (new VarDirectory())->init();

        self::assertTrue(file_exists($varPath));
        self::assertTrue(file_exists($varPath . '/cache'));
        self::assertTrue(file_exists($varPath . '/log'));
    }

    /**
     * @dataProvider availableCacheManagerDataProvider
     * @param string $cacheClass
     * @return void
     */
    public function testCache(string $cacheClass): void {
        $cache = new $cacheClass();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $secondInstanceCacheTest = new $cacheClass();
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