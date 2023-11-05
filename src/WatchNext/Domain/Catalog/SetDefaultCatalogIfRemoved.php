<?php

namespace WatchNext\WatchNext\Domain\Catalog;

use WatchNext\WatchNext\Domain\Catalog\EventSubscriber\CreateDefaultUserCatalogEventSubscriber;
use WatchNext\WatchNext\Domain\User\Query\UserCreatedEvent;

class SetDefaultCatalogIfRemoved
{
    public function __construct(
        private CatalogRepository $catalogRepository,
        private CreateDefaultUserCatalogEventSubscriber $createDefaultUserCatalogCommand
    ) {
    }

    public function execute(?int $userId): void
    {
        if (!$userId) {
            return;
        }

        /** @var Catalog[] $userCatalogs */
        $userCatalogs = $this->catalogRepository->findAllForUser($userId);

        if (!empty($userCatalogs)) {
            $this->catalogRepository->setAsDefault(new CatalogUser($userCatalogs[0]->getId(), $userId));
        } else {
            $this->createDefaultUserCatalogCommand->execute(new UserCreatedEvent($userId));
        }
    }
}
