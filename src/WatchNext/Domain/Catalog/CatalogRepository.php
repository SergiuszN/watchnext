<?php

namespace WatchNext\WatchNext\Domain\Catalog;

interface CatalogRepository {
    public function save(Catalog $catalog): void;
    public function hasAccess(CatalogUser $catalogUser): bool;
    public function addAccess(CatalogUser $catalogUser): void;
    public function removeAccess(CatalogUser $catalogUser): void;
    public function addItem(CatalogItem $catalogItem): void;
    public function find(int $catalogId): ?Catalog;
    public function findDefaultForUser(?int $userId): ?Catalog;
    public function findAllForUser(?int $userId): array;
    public function setAsDefault(CatalogUser $catalogUser): void;

    /** @return CatalogUser[]|array List of removed default catalogs */
    public function remove(Catalog $catalog): array;
}