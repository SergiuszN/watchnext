<?php

namespace WatchNext\Engine\Cache;

class ApcuCache implements CacheInterface
{
    public function get(string $key, callable $callback, int $ttl = null): mixed
    {
        $data = apcu_fetch($key, $success);

        if ($success) {
            return $data;
        }

        $data = $callback();
        apcu_store($key, $data, $ttl ?? 0);

        return $data;
    }

    public function read(string $key, mixed $default = null): mixed
    {
        $data = apcu_fetch($key, $success);

        return $success ? $data : $default;
    }

    public function set(string $key, mixed $data, int $ttl = null): void
    {
        apcu_store($key, $data, $ttl ?? 0);
    }

    public function delete(string $key): void
    {
        apcu_delete($key);
    }

    public function has(string $key): bool
    {
        return (bool) apcu_exists($key);
    }

    public function clearAll(): void
    {
        apcu_clear_cache();
    }
}
