<?php

namespace WatchNext\Engine\Cache;

use Redis;
use RedisException;

class RedisCache implements CacheInterface {
    private static ?Redis $redis = null;

    /**
     * @throws RedisException
     */
    public function __construct() {
        if (self::$redis) {
            return;
        }

        $uri = explode('@', $_ENV['REDIS_URL']);
        $hostPort = explode(':', $uri[0]);

        self::$redis = new Redis();
        self::$redis->connect($hostPort[0], $hostPort[1]);
        self::$redis->auth($uri[1]);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function get(string $key, callable $callback, ?int $ttl = null): mixed {
        $redisKey = $this->getCacheKey($key);

        if ($this->has($key)) {
            return self::$redis->get($redisKey);
        }

        $data = $callback();
        self::$redis->set($redisKey, $data);

        if ($ttl) {
            self::$redis->expire($redisKey, $ttl);
        }

        return $data;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function delete(string $key): void {
        self::$redis->del($this->getCacheKey($key));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function has(string $key): bool {
        return self::$redis->exists($this->getCacheKey($key));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function clearAll(): void {
        self::$redis->del($this->getCacheKey('*'));
    }

    private function getCacheKey($key): string {
        return 'cache:' . $key;
    }
}