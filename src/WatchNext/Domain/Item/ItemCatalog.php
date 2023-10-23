<?php

namespace WatchNext\WatchNext\Domain\Item;

class ItemCatalog {
    public function __construct(private int $itemId, private int $catalogId) {
    }

    public function getItemId(): int {
        return $this->itemId;
    }

    public function setItemId(int $itemId): ItemCatalog {
        $this->itemId = $itemId;
        return $this;
    }

    public function getCatalogId(): int {
        return $this->catalogId;
    }

    public function setCatalogId(int $catalogId): ItemCatalog {
        $this->catalogId = $catalogId;
        return $this;
    }
}