<?php

return [
    \WatchNext\Engine\Cache\CacheInterface::class => fn () => new \WatchNext\Engine\Cache\MemcachedCache(),
    \WatchNext\WatchNext\Application\Controller\HomepageController::class => \DI\autowire(\WatchNext\WatchNext\Application\Controller\HomepageController::class),
    \WatchNext\WatchNext\Application\Controller\SecurityController::class => \DI\autowire(\WatchNext\WatchNext\Application\Controller\SecurityController::class),

    \WatchNext\WatchNext\Domain\User\UserRepository::class => \DI\autowire(\WatchNext\WatchNext\Infrastructure\User\UserDBALRepository::class),
];