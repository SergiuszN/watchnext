<?php

namespace WatchNext\WatchNext\Domain\Item;

use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Template\PaginationCollection;

interface ItemRepository {
    public function save(Item $item): void;
    public function find(int $id): ?Item;
    public function findAllForUser(?int $owner);
    public function findPage(int $page, int $limit, int $catalog, Request $request): PaginationCollection;
}