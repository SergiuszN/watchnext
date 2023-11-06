<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use Exception;
use WatchNext\Engine\Database\PaginationQuery;
use WatchNext\Engine\Database\QueryBuilder;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Template\PaginationCollection;
use WatchNext\WatchNext\Domain\Item\Item;
use WatchNext\WatchNext\Domain\Item\ItemRepository;

class ItemPDORepository extends PDORepository implements ItemRepository
{
    public function save(Item $item): void
    {
        if ($item->getId() === null) {
            $this->database->query((new QueryBuilder())
                ->insert('item')
                ->addValue('title')
                ->addValue('url')
                ->addValue('description')
                ->addValue('image')
                ->addValue('owner')
                ->addValue('catalog')
                ->addValue('added_at')
                ->addValue('is_watched')
                ->addValue('note')
                ->setParameters($item->toDatabase())
            );

            $item->setId($this->database->getLastInsertId());
        } else {
            $this->database->query((new QueryBuilder())
                ->update('item')
                ->addSet('title')
                ->addSet('url')
                ->addSet('description')
                ->addSet('image')
                ->addSet('owner')
                ->addSet('catalog')
                ->addSet('added_at')
                ->addSet('is_watched')
                ->addSet('note')
                ->setParameters($item->toDatabase())
                ->andWhere('id = :id')
                ->setParameter('id', $item->getId())
            );
        }
    }

    /**
     * @throws Exception
     */
    public function find(int $id): ?Item
    {
        $data = $this->database->prepare('SELECT * FROM `item` WHERE id=:id')
            ->execute(['id' => $id])
            ->fetch();

        return $data ? Item::fromDatabase($data) : null;
    }

    /**
     * @throws Exception
     */
    public function findAllForUser(?int $owner): array
    {
        $data = $this->database->prepare('SELECT * FROM `item` WHERE owner=:owner ORDER BY id DESC')
            ->execute(['owner' => $owner])
            ->fetchAll();

        return array_map(fn (array $item) => Item::fromDatabase($item), $data);
    }

    /**
     * @throws Exception
     */
    public function findCatalogPage(int $page, int $limit, int $catalog, Request $request): PaginationCollection
    {
        $query = (new QueryBuilder())
            ->from('item')
            ->andWhere('catalog = :catalog')
            ->setParameter('catalog', $catalog)
            ->addOrderBy('added_at', 'DESC');

        return (new PaginationQuery(
            $this->database,
            $query,
            Item::class,
            '*',
            'COUNT(id)',
            $limit,
            $page
        ))->getPagination();
    }

    public function hasAccess(int $itemId, int $userId): bool
    {
        return $this->database->query((new QueryBuilder())
            ->select('COUNT(i.id)')
            ->from('item as i')
            ->addLeftJoin('catalog_user as cu', 'i.catalog = cu.catalog')
            ->andWhere('i.id = :itemId')
            ->andWhere('cu.user = :userId')
            ->setParameter('itemId', $itemId)
            ->setParameter('userId', $userId)
        )->fetchSingle() > 0;
    }

    public function findSearchPage(int $page, int $limit, int $userId, Request $request): PaginationCollection
    {
        $query = (new QueryBuilder())
            ->from('item as i')
            ->addLeftJoin('catalog_user as cu', 'i.catalog = cu.catalog')
            ->andWhere('cu.user = :userId')
            ->setParameter('userId', $userId)
            ->addOrderBy('i.id', 'DESC');

        if ($request->get('search')) {
            $query
                ->andWhere('(i.title LIKE :search1 OR i.url LIKE :search2 OR i.description LIKE :search3)')
                ->setParameter('search1', "%{$request->get('search')}%")
                ->setParameter('search2', "%{$request->get('search')}%")
                ->setParameter('search3', "%{$request->get('search')}%");
        }

        return (new PaginationQuery(
            $this->database,
            $query,
            Item::class,
            'i.*',
            'COUNT(i.id)',
            $limit,
            $page
        ))->getPagination();
    }

    public function delete(Item $item): void
    {
        $this->database->query((new QueryBuilder())
            ->delete('item')
            ->andWhere('id = :id')
            ->setParameter('id', $item->getId())
        );
    }
}
