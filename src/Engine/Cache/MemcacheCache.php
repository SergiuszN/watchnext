<?php

namespace WatchNext\Engine\Cache;

use Memcache;

class MemcacheCache implements CacheInterface {
    private static ?Memcache $memcache = null;

    public function __construct() {
        if (self::$memcache) {
            return;
        }

        self::$memcache = new Memcache();
    }

    public function get(string $key, callable $callback, ?int $ttl = null): mixed {
        if (self::$memcache->get($key)) {
            return self::$memcache->get($key);
        }

        $data = $callback();
        self::$memcache->set($key, $data, $ttl ?: 0);

        return $data;
    }

    public function delete(string $key): void {
        self::$memcache->delete($key);
    }

    public function has(string $key): bool {
        return self::$memcache->get($key) !== false;
    }

    public function clearAll(): void {
        self::$memcache->flush();
    }
}