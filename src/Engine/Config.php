<?php

namespace WatchNext\Engine;

use Exception;

class Config
{
    private static array $configs = [];

    public function get(string $name): array
    {
        if (isset(self::$configs[$name])) {
            return self::$configs[$name];
        }

        $path = ROOT_PATH . "/config/{$name}";

        if (!file_exists($path)) {
            throw new Exception("Config '$name' is not exist!");
        }

        self::$configs[$name] = require $path;

        return self::$configs[$name];
    }
}
