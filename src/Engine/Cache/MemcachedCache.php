<?php

namespace WatchNext\Engine\Cache;

use Memcached;

class MemcachedCache implements CacheInterface
{
    private static ?Memcached $memcached = null;

    public function __construct()
    {
        if (self::$memcached) {
            return;
        }

        self::$memcached = new Memcached();
        self::$memcached->addServer(...explode(':', $_ENV['MEMCACHED_URL']));
    }

    public function get(string $key, callable $callback, int $ttl = null): mixed
    {
        if (self::$memcached->get($key)) {
            return self::$memcached->get($key);
        }

        $data = $callback();
        self::$memcached->set($key, $data, $ttl > 0 ? $ttl : 0);

        return $data;
    }

    public function read(string $key, mixed $default = null): mixed
    {
        return self::$memcached->get($key) ?? $default;
    }

    public function set(string $key, mixed $data, int $ttl = null): void
    {
        self::$memcached->set($key, $data, $ttl > 0 ? $ttl : 0);
    }

    public function delete(string $key): void
    {
        self::$memcached->delete($key);
    }

    public function has(string $key): bool
    {
        return self::$memcached->get($key) !== false;
    }

    public function clearAll(): void
    {
        self::$memcached->flush();
    }
}
