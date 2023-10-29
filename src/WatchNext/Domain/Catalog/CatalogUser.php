<?php

namespace WatchNext\WatchNext\Domain\Catalog;

class CatalogUser {
    public function __construct(public int $catalog, public int $user) {
    }

    public static function fromDatabase(array $item): CatalogUser {
        return new CatalogUser($item['catalog'], $item['user']);
    }

    public function toDatabase(): array {
        return [
            'catalog' => $this->catalog,
            'user' => $this->user,
        ];
    }
}