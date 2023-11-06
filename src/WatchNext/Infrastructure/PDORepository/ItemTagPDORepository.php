<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\QueryBuilder;
use WatchNext\WatchNext\Domain\Item\ItemTag;
use WatchNext\WatchNext\Domain\Item\ItemTagRepository;

class ItemTagPDORepository extends PDORepository implements ItemTagRepository
{
    public function __construct(Database $database)
    {
        parent::__construct($database);
    }

    public function findAllUniqueTagsForUser(int $userId): array
    {
        return array_map(fn ($row) => $row['value'], $this->database->query((new QueryBuilder())
            ->select('it.value as value')
            ->from('item_tag as it')
            ->addLeftJoin('item as i', 'i.id = it.item')
            ->addLeftJoin('catalog_user as cu', 'i.catalog = cu.catalog')
            ->andWhere('cu.user = :userId')
            ->addGroupBy('it.value')
        )->fetchAll());
    }

    public function save(ItemTag $tag): void
    {
        if ($tag->getId() === null) {
            $this->database->query((new QueryBuilder())
                ->insert('item_tag')
                ->addValue('item')
                ->addValue('value')
                ->addValue('created_at')
                ->setParameters($tag->toDatabase())
            );

            $tag->setId($this->database->getLastInsertId());
        } else {
            $this->database->query((new QueryBuilder())
                ->update('item_tag')
                ->addSet('item')
                ->addSet('value')
                ->addSet('created_at')
                ->setParameters($tag->toDatabase())
                ->andWhere('id = :id')
                ->setParameter('id', $tag->getId())
            );
        }
    }

    public function remove(ItemTag $tag): void
    {
        $this->database->query((new QueryBuilder())
            ->delete('item_tag')
            ->andWhere('id = :id')
            ->setParameter('id', $tag->getId())
        );
    }
}
