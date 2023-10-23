<?php

namespace WatchNext\WatchNext\Domain\Item;

interface ItemRepository {
    public function save(Item $item): void;
    public function find(int $id): ?Item;
    public function findAllForUser(?int $getUserId);
}