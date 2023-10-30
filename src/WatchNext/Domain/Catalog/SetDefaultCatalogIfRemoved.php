<?php

namespace WatchNext\WatchNext\Domain\Catalog;

use WatchNext\WatchNext\Domain\Catalog\Command\CreateDefaultUserCatalogCommand;
use WatchNext\WatchNext\Domain\User\Query\UserCreatedQuery;

class SetDefaultCatalogIfRemoved {
    public function __construct(
        private CatalogRepository $catalogRepository,
        private CreateDefaultUserCatalogCommand $createDefaultUserCatalogCommand
    ) {
    }

    public function execute(?int $userId): void {
        if (!$userId) {
            return;
        }

        /** @var Catalog[] $userCatalogs */
        $userCatalogs = $this->catalogRepository->findAllForUser($userId);

        if (!empty($userCatalogs)) {
            $this->catalogRepository->setAsDefault(new CatalogUser($userCatalogs[0]->getId(), $userId));
        } else {
            $this->createDefaultUserCatalogCommand->execute(new UserCreatedQuery($userId));
        }
    }
}