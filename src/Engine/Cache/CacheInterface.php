<?php

namespace WatchNext\Engine\Cache;

interface CacheInterface {
    public function get(string $key, callable $callback, ?int $ttl = null): mixed;
    public function read(string $key): mixed;
    public function set(string $key, mixed $data, ?int $ttl = null): mixed;
    public function delete(string $key): void;
    public function has(string $key): bool;
    public function clearAll(): void;
}