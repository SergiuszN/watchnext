<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
return [
    \WatchNext\Engine\Cache\CacheInterface::class => fn () => new \WatchNext\Engine\Cache\MemcachedCache(),
    \WatchNext\WatchNext\Application\Controller\HomepageController::class => \DI\autowire(\WatchNext\WatchNext\Application\Controller\HomepageController::class),
    \WatchNext\WatchNext\Application\Controller\SecurityController::class => \DI\autowire(\WatchNext\WatchNext\Application\Controller\SecurityController::class),
    \WatchNext\WatchNext\Application\Controller\CatalogController::class => \DI\autowire(\WatchNext\WatchNext\Application\Controller\CatalogController::class),
    \WatchNext\WatchNext\Domain\User\UserRepository::class => \DI\autowire(\WatchNext\WatchNext\Infrastructure\PDORepository\UserPDORepository::class),
    \WatchNext\WatchNext\Domain\Item\ItemRepository::class => \DI\autowire(\WatchNext\WatchNext\Infrastructure\PDORepository\ItemPDORepository::class),
    \WatchNext\WatchNext\Domain\Catalog\CatalogRepository::class => \DI\autowire(\WatchNext\WatchNext\Infrastructure\PDORepository\CatalogPDORepository::class),
    \WatchNext\WatchNext\Domain\Catalog\CatalogVoter::class => \DI\autowire(\WatchNext\WatchNext\Domain\Catalog\CatalogVoter::class),
    \WatchNext\WatchNext\Domain\Catalog\CatalogMenuLoader::class => \DI\autowire(\WatchNext\WatchNext\Domain\Catalog\CatalogMenuLoader::class),
    \WatchNext\WatchNext\Domain\Catalog\EventSubscriber\CreateDefaultUserCatalogEventSubscriber::class => \DI\autowire(\WatchNext\WatchNext\Domain\Catalog\EventSubscriber\CreateDefaultUserCatalogEventSubscriber::class),
    \WatchNext\WatchNext\Domain\Catalog\SetDefaultCatalogIfRemoved::class => \DI\autowire(\WatchNext\WatchNext\Domain\Catalog\SetDefaultCatalogIfRemoved::class),
    \WatchNext\WatchNext\Domain\User\Form\UserRegisterForm::class => \DI\autowire(\WatchNext\WatchNext\Domain\User\Form\UserRegisterForm::class),
    \WatchNext\WatchNext\Domain\Catalog\Form\AddEditCatalogForm::class => \DI\autowire(\WatchNext\WatchNext\Domain\Catalog\Form\AddEditCatalogForm::class),
    \WatchNext\Engine\Event\EventDispatcherInterface::class => \DI\autowire(\WatchNext\Engine\Event\DatabaseEventDispatcher::class),
    \WatchNext\WatchNext\Domain\Item\ItemTagRepository::class => \DI\autowire(\WatchNext\WatchNext\Infrastructure\PDORepository\ItemTagPDORepository::class),
    \WatchNext\WatchNext\Domain\Item\Form\UpdateTagsForm::class => \DI\autowire(\WatchNext\WatchNext\Domain\Item\Form\UpdateTagsForm::class),
    \WatchNext\WatchNext\Domain\Item\Form\MoveOrCopyItemForm::class => \DI\autowire(\WatchNext\WatchNext\Domain\Item\Form\MoveOrCopyItemForm::class),
];
