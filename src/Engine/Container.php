<?php

namespace WatchNext\Engine;

use DI\Container as DIContainer;
use DI\ContainerBuilder;
use Exception;
use function DI\autowire;

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

        $kernelConfig = $this->getKernelDI();
        $baseConfig = require __DIR__ . '/../../config/di/di.php';
        $envConfig = require __DIR__ . "/../../config/di/di.{$env}.php";

        $builder = new ContainerBuilder();
        $builder->addDefinitions(array_merge($kernelConfig, $baseConfig, $envConfig));

        if ($env === 'prod') {
            $builder->enableCompilation(__DIR__ . '/../../cache/var/di-cache');
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

    private function getKernelDI(): array {
        return [
            'root.dir' => realpath(__DIR__ . '/../../'),

            \WatchNext\Engine\Container::class => fn () => new \WatchNext\Engine\Container(),
            \WatchNext\Engine\Database\Database::class => fn () => new \WatchNext\Engine\Database\Database(),
            \WatchNext\Engine\TemplateEngine::class => fn () => new \WatchNext\Engine\TemplateEngine(),

            \WatchNext\Engine\Cache\CacheInterface::class => fn () => new \WatchNext\Engine\Cache\MemcacheCache(),
            \WatchNext\Engine\Router\RouteGenerator::class => autowire(\WatchNext\Engine\Router\RouteGenerator::class),
            \WatchNext\Engine\Logger::class => fn() => new \WatchNext\Engine\Logger(),
        ];
    }
}