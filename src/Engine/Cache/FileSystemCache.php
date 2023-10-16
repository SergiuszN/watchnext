<?php

namespace WatchNext\Engine\Cache;

class FileSystemCache implements CacheInterface {
    private string $cachePath;
    private array $storage = [];
    private const TTL_4000 = 64085164120;

    public function __construct() {
        $this->cachePath = __DIR__ . '/../../../var/cache/file-cache.cache';

        if (file_exists(!$this->cachePath)) {
            touch($this->cachePath);
        }
    }

    private function read(): void {
        $data = file_get_contents($this->cachePath);
        $this->storage = !empty($data) ? unserialize($data) : [];
    }

    private function save(): void {
        file_put_contents($this->cachePath, serialize($this->storage));
    }

    public function get(string $key, callable $callback, ?int $ttl = null): mixed {
        $this->read();

        if ($this->has($key, false)) {
            return $this->storage[$key]['data'];
        }

        $this->storage[$key]['data'] = $callback();
        $this->storage[$key]['ttl'] = $ttl ? $ttl + time() : self::TTL_4000;

        $this->save();

        return $this->storage[$key]['data'];
    }

    public function set(string $key, mixed $data, ?int $ttl = null): mixed {
        $this->read();

        $this->storage[$key]['data'] = $data;
        $this->storage[$key]['ttl'] = $ttl ? $ttl + time() : self::TTL_4000;

        $this->save();

        return $this->storage[$key]['data'];
    }

    public function delete(string $key): void {
        $this->read();
        unset($this->storage[$key]);
        $this->save();
    }

    public function has(string $key, bool $read = true): bool {
        if ($read) {
            $this->read();
        }

        return isset($this->storage[$key]) && $this->storage[$key]['ttl'] > time();
    }

    public function clearAll(): void {
        $this->read();
        $this->storage = [];
        $this->save();
    }
}