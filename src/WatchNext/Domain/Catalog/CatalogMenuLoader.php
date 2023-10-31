<?php

namespace WatchNext\WatchNext\Domain\Catalog;

use WatchNext\Engine\Session\Security;

class CatalogMenuLoader {
    private ?Catalog $defaultUserCatalog = null;

    private static bool $inited = false;

    public function __construct(
        private readonly CatalogRepository $catalogRepository,
        private readonly Security          $security,
    ) {
    }

    public function get(): array {
        $this->init();
        return $this->catalogRepository->findAllForUser($this->security->getUserId());
    }

    public function isDefault(Catalog $catalog): bool {
        $this->init();
        return $this->defaultUserCatalog?->getId() === $catalog->getId();
    }

    private function init(): void {
        if (self::$inited) {
            return;
        }

        $userId = $this->security->getUserId();
        $this->defaultUserCatalog = $this->catalogRepository->findDefaultForUser($userId);
        self::$inited = true;
    }
}