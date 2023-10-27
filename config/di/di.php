<?php

return [
    \WatchNext\Engine\Cache\CacheInterface::class => fn () => new \WatchNext\Engine\Cache\MemcachedCache(),
    \WatchNext\WatchNext\Application\Controller\HomepageController::class => \DI\autowire(\WatchNext\WatchNext\Application\Controller\HomepageController::class),
    \WatchNext\WatchNext\Application\Controller\SecurityController::class => \DI\autowire(\WatchNext\WatchNext\Application\Controller\SecurityController::class),
    \WatchNext\WatchNext\Application\Controller\CatalogController::class => \DI\autowire(\WatchNext\WatchNext\Application\Controller\CatalogController::class),

    \WatchNext\WatchNext\Domain\User\UserRepository::class => \DI\autowire(\WatchNext\WatchNext\Infrastructure\PDORepository\UserPDORepository::class),
    \WatchNext\WatchNext\Domain\Item\ItemRepository::class => \DI\autowire(\WatchNext\WatchNext\Infrastructure\PDORepository\ItemPDORepository::class),
    \WatchNext\WatchNext\Domain\Catalog\CatalogRepository::class => \DI\autowire(\WatchNext\WatchNext\Infrastructure\PDORepository\CatalogPDORepository::class),

    \WatchNext\WatchNext\Domain\Catalog\CatalogVoter::class => \DI\autowire(\WatchNext\WatchNext\Domain\Catalog\CatalogVoter::class),
    \WatchNext\WatchNext\Domain\Catalog\CatalogMenuLoader::class => \DI\autowire(\WatchNext\WatchNext\Domain\Catalog\CatalogMenuLoader::class)
];