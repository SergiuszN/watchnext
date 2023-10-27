<?php

namespace WatchNext\WatchNext\Domain\Catalog;

use WatchNext\Engine\Session\Auth;

readonly class CatalogMenuLoader {
    public function __construct(
        private CatalogRepository $catalogRepository,
        private Auth $auth,
    ) {
    }

    public function get(): array {
        $userId = $this->auth->getUserId();
        return $this->catalogRepository->findAllForUser($userId);
    }
}