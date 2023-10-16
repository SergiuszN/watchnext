<?php

namespace WatchNext\Engine;

class Config {
    private static array $cache = [];
    public function get(string $name): array {
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        $path = "{$this->getRootPath()}/config/{$name}";

        if (!file_exists($path)) {
            throw new \Exception("Config '$name' is not exist!");
        }

        self::$cache[$name] = require $path;
        return self::$cache[$name];
    }

    public function getRootPath(): string {
        if (isset(self::$cache['path.root'])) {
            return self::$cache['path.root'];
        }

        self::$cache['path.root'] = realpath(__DIR__ . '/../../');
        return self::$cache['path.root'];
    }

    public function getCachePath(): string {
        if (isset(self::$cache['path.cache'])) {
            return self::$cache['path.cache'];
        }

        self::$cache['path.cache'] = "{$this->getRootPath()}/var/cache";
        return self::$cache['path.cache'];
    }

    public function getLogPath(): string {
        if (isset(self::$cache['path.log'])) {
            return self::$cache['path.log'];
        }

        self::$cache['path.log'] = "{$this->getRootPath()}/var/log";
        return self::$cache['path.log'];
    }
}