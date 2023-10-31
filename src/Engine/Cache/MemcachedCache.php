<?php

namespace WatchNext\Engine\Cache;

use Memcached;

class MemcachedCache implements CacheInterface
{
    private ?Memcached $memcached;

    public function __construct()
    {
        $this->memcached = new Memcached();
        $this->memcached->addServer(...explode(':', $_ENV['MEMCACHED_URL']));
    }

    public function get(string $key, callable $callback, int $ttl = null): mixed
    {
        if ($this->memcached->get($key)) {
            return $this->memcached->get($key);
        }

        $data = $callback();
        $this->memcached->set($key, $data, $ttl > 0 ? $ttl : 0);

        return $data;
    }

    public function read(string $key, mixed $default = null): mixed
    {
        return $this->memcached->get($key) ?? $default;
    }

    public function set(string $key, mixed $data, int $ttl = null): void
    {
        $this->memcached->set($key, $data, $ttl > 0 ? $ttl : 0);
    }

    public function delete(string $key): void
    {
        $this->memcached->delete($key);
    }

    public function has(string $key): bool
    {
        return $this->memcached->get($key) !== false;
    }

    public function clearAll(): void
    {
        $this->memcached->flush();
    }
}
