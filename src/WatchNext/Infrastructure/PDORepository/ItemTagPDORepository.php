<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use DateTimeImmutable;
use Exception;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\QueryBuilder;
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
            ->setParameter('userId', $userId)
            ->addGroupBy('it.value')
        )->fetchAll());
    }

    public function updateForItem(int $itemId, array $tags): void
    {
        try {
            $this->database->transactionBegin();

            $this->database->query((new QueryBuilder())
                ->delete('item_tag')
                ->andWhere('item = :item')
                ->setParameter('item', $itemId)
            );

            $date = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            foreach ($tags as $tag) {
                $this->database->query((new QueryBuilder())
                    ->insert('item_tag')
                    ->addValue('item')
                    ->addValue('value')
                    ->addValue('created_at')
                    ->setParameters(['item' => $itemId, 'value' => $tag, 'created_at' => $date])
                );
            }

            $this->database->transactionCommit();
        } catch (Exception $exception) {
            $this->database->transactionRollback();
        }
    }
}
