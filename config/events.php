<?php

return [
    \WatchNext\WatchNext\Domain\User\Query\UserCreatedEvent::class => \WatchNext\WatchNext\Domain\Catalog\EventSubscriber\CreateDefaultUserCatalogEventSubscriber::class,
    \WatchNext\WatchNext\Domain\Catalog\Event\TestCatalogEvent::class => \WatchNext\WatchNext\Domain\Catalog\EventSubscriber\TestCatalogEventSubscriber::class,
];
