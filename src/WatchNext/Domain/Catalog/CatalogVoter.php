<?php

namespace WatchNext\WatchNext\Domain\Catalog;

use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Session\Auth;

readonly class CatalogVoter {
    public function __construct(
        private Auth $auth
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function throwIfNotGranted(?Catalog $catalog): void {
        if (!$this->isGranted($catalog)) {

            throw new NotFoundException();
        }
    }

    /**
     * @throws NotFoundException
     */
    public function isGranted(?Catalog $catalog): bool {
        if (!$catalog) {
            throw new NotFoundException();
        }

        $userId = $this->auth->getUserId();
        return $catalog->getOwner() === $userId;
    }
}