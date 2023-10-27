<?php

namespace WatchNext\WatchNext\Domain\Catalog;

interface CatalogRepository {
    public function save(Catalog $catalog): void;
    public function find(int $catalogId): ?Catalog;
    public function findDefaultForUser(?int $ownerId): ?Catalog;
    public function findAllForUser(?int $userId): array;
}