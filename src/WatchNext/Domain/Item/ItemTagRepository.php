<?php

namespace WatchNext\WatchNext\Domain\Item;

interface ItemTagRepository
{
    /** @return array|string[] */
    public function findAllUniqueTagsForUser(int $userId): array;

    /**
     * @param int $itemId
     * @param string[] $tags
     * @return void
     */
    public function updateForItem(int $itemId, array $tags): void;
}
