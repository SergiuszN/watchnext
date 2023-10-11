<?php

return [
    'root.dir' => realpath(__DIR__ . '/../../'),

    \WatchNext\Engine\Container::class => fn () => new \WatchNext\Engine\Container(),
    \WatchNext\Engine\Database::class => fn () => new \WatchNext\Engine\Database(),
    \WatchNext\Engine\TemplateEngine::class => fn () => new \WatchNext\Engine\TemplateEngine(),

    \WatchNext\Engine\Cache\CacheInterface::class => fn () => new \WatchNext\Engine\Cache\MemcacheCache(),
    \WatchNext\Engine\Router\RouteGenerator::class => DI\autowire(\WatchNext\Engine\Router\RouteGenerator::class),
];