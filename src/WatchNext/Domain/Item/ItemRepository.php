<?php

namespace WatchNext\WatchNext\Domain\Item;

use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Template\PaginationCollection;

interface ItemRepository
{
    public function save(Item $item): void;

    public function find(int $id): ?Item;

    public function findCatalogPage(int $page, int $limit, int $catalog, Request $request): PaginationCollection;

    public function findSearchPage(int $page, int $limit, int $userId, Request $request): PaginationCollection;

    public function hasAccess(int $itemId, int $userId): bool;

    public function remove(Item $item): void;
}
