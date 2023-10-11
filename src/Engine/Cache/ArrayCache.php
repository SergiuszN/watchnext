<?php

namespace WatchNext\Engine\Cache;

class ArrayCache implements CacheInterface {
    private static array $storage = [];

    private const TTL_4000 = 64085164120;

    public function get(string $key, callable $callback, ?int $ttl = null): mixed {
        if ($this->has($key)) {
            return self::$storage[$key]['data'];
        }

        self::$storage[$key]['data'] = $callback();
        self::$storage[$key]['ttl'] = $ttl ? $ttl + time() : self::TTL_4000;

        return self::$storage[$key]['data'];
    }

    public function delete(string $key): void {
        unset(self::$storage[$key]);
    }

    public function has(string $key): bool {
        return isset(self::$storage[$key]) && self::$storage[$key]['ttl'] > time();
    }

    public function clearAll(): void {
        self::$storage = [];
    }
}