<?php

namespace WatchNext\WatchNext\Domain\Item;

interface ItemTagRepository
{
    /** @return array|string[] */
    public function findAllUniqueTagsForUser(int $userId): array;

    public function save(ItemTag $tag): void;

    public function remove(ItemTag $tag): void;
}
