<?php

namespace WatchNext\WatchNext\Domain\Catalog;

use WatchNext\Engine\Session\Auth;

class CatalogMenuLoader {
    private ?Catalog $defaultUserCatalog = null;

    public function __construct(
        private readonly CatalogRepository $catalogRepository,
        private readonly Auth $auth,
    ) {
    }

    public function get(): array {
        $userId = $this->auth->getUserId();
        $this->defaultUserCatalog = $this->catalogRepository->findDefaultForUser($userId);
        return $this->catalogRepository->findAllForUser($userId);
    }

    public function isDefault(Catalog $catalog): bool {
        return $this->defaultUserCatalog?->getId() === $catalog->getId();
    }
}