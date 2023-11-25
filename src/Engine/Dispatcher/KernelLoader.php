<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use WatchNext\Engine\Container;
use WatchNext\Engine\Env;

class KernelLoader
{
    /**
     * @throws Exception
     */
    public function load(string $env = null): Container
    {
        define('ROOT_PATH', realpath(__DIR__ . '/../../../'));

        (new Env())->load();

        if ($env !== null) {
            $_ENV['APP_ENV'] = $env;
        }

        define('ENV', $_ENV['APP_ENV']);

        $this->createVarDirectory();

        return (new Container())->init();
    }

    public function createVarDirectory(): void
    {
        $dir = ROOT_PATH . '/var';

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        if (!file_exists($dir . '/cache')) {
            mkdir($dir . '/cache');
        }

        if (!file_exists($dir . '/log')) {
            mkdir($dir . '/log');
        }
    }
}
