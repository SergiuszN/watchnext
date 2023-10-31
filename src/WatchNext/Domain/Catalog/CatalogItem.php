<?php

namespace WatchNext\WatchNext\Domain\Catalog;

readonly class CatalogItem
{
    public function __construct(public int $item, public int $catalog)
    {
    }

    public static function fromDatabase(array $item): CatalogItem
    {
        return new CatalogItem($item['item'], $item['catalog']);
    }

    public function toDatabase(): array
    {
        return [
            'item' => $this->item,
            'catalog' => $this->catalog,
        ];
    }
}
