<?php

namespace WatchNext\Engine;

use DI\Container as DIContainer;
use DI\ContainerBuilder;
use Exception;

class Container {
    private static ?DIContainer $diContainer = null;

    /**
     * @throws Exception
     */
    public function init(): void {
        if (self::$diContainer) {
            throw new Exception('Container already created!');
        }

        $env = $_ENV['APP_ENV'];

        $baseConfig = require __DIR__ . '/../../config/di/di.php';
        $envConfig = require __DIR__ . "/../../config/di/di.{$env}.php";

        $builder = new ContainerBuilder();
        $builder->addDefinitions(array_merge($baseConfig, $envConfig));

        if ($env === 'prod') {
            $builder->enableCompilation(__DIR__ . '/../../var/di-cache');
        }

        self::$diContainer = $builder->build();
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @template T
     * @param string|class-string<T> $id Entry name or a class name.
     *
     * @return mixed|T
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function get(string $id): mixed {
        /** @noinspection PhpUnhandledExceptionInspection */
        return self::$diContainer->get($id);
    }
}