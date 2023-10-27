<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use Exception;
use WatchNext\Engine\Database\QueryBuilder;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Template\PaginationCollection;
use WatchNext\WatchNext\Domain\Item\Item;
use WatchNext\WatchNext\Domain\Item\ItemRepository;

class ItemPDORepository extends PDORepository implements ItemRepository {

    public function save(Item $item): void {
        if ($item->getId() === null) {
            $this->database->prepare("
                INSERT INTO `item` (
                    `title`, 
                    `url`,
                    `description`,
                    `image`,
                    `owner`, 
                    `added_at`
                )
                VALUES (
                    :title, 
                    :url,
                    :description,
                    :image,
                    :owner, 
                    :added_at
                )
            ")->execute($item->toDatabase());
            $item->setId($this->database->getLastInsertId());
        } else {
            $this->database->prepare("
                UPDATE `item` SET
                    `title` = :title,
                    `url` = :url,
                    `description` = :description,
                    `image` = :image,
                    `owner` = :owner,
                    `added_at` = :added_at
                WHERE `id` = :id
            ")->execute(array_merge($item->toDatabase(), ['id' => $item->getId()]));
        }
    }

    /**
     * @throws Exception
     */
    public function find(int $id): ?Item {
        $data = $this->database->prepare("SELECT * FROM `item` WHERE id=:id")
            ->execute(['id' => $id])
            ->fetch();

        return $data ? Item::fromDatabase($data) : null;
    }

    /**
     * @throws Exception
     */
    public function findAllForUser(?int $owner): array {
        $data = $this->database->prepare("SELECT * FROM `item` WHERE owner=:owner ORDER BY id DESC")
            ->execute(['owner' => $owner])
            ->fetchAll();

        return array_map(fn (array $item) => Item::fromDatabase($item), $data);
    }

    /**
     * @throws Exception
     */
    public function findPage(int $page, int $limit, int $catalog, Request $request): PaginationCollection {
        $countQuery = (new QueryBuilder())
            ->select('COUNT(i.id)')
            ->from('item as i')
            ->addLeftJoin('catalog_item as ci', 'ci.item = i.id')
            ->addLeftJoin('catalog as c', 'c.id = ci.catalog')
            ->andWhere('c.id = :catalog')
            ->setParameter('catalog', $catalog);

        $count = (int) $this->database
            ->prepare($countQuery->getSql())
            ->execute($countQuery->getParams())
            ->fetchSingle();

        $query = (clone $countQuery)
            ->select('i.*')
            ->limit($limit, $limit * ($page - 1));

        $items = $this->database
            ->prepare($query->getSql())
            ->execute($query->getParams())
            ->fetchAll();

        return new PaginationCollection(
            $page,
            $limit,
            ceil(($count) / $limit),
            $items
        );
    }
}