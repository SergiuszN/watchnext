<?php

namespace WatchNext\WatchNext\Domain\Item;

interface ItemTagRepository
{
    /** @return array|string[] */
    public function findAllUniqueTagsForUser(int $userId): array;

    /**
     * @param string[] $tags
     */
    public function updateForItem(int $itemId, array $tags): void;
}
