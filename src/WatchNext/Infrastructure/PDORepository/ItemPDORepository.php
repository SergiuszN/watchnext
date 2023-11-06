<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use Exception;
use WatchNext\Engine\Database\PaginationQuery;
use WatchNext\Engine\Database\QueryBuilder;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Template\PaginationCollection;
use WatchNext\WatchNext\Domain\Catalog\Catalog;
use WatchNext\WatchNext\Domain\Item\Item;
use WatchNext\WatchNext\Domain\Item\ItemRepository;
use WatchNext\WatchNext\Domain\Item\ItemTag;

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
    public function findCatalogPage(int $page, int $limit, int $catalog, Request $request): PaginationCollection
    {
        $query = (new QueryBuilder())
            ->from('item')
            ->andWhere('catalog = :catalog')
            ->setParameter('catalog', $catalog)
            ->addOrderBy('added_at', 'DESC');

        $pagination = (new PaginationQuery(
            $this->database,
            $query,
            Item::class,
            '*',
            'COUNT(id)',
            $limit,
            $page
        ))->getPagination();

        $this->loadTags($pagination->items);

        return $pagination;
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

    /**
     * @throws Exception
     */
    public function findSearchPage(int $page, int $limit, int $userId, Request $request): PaginationCollection
    {
        $query = (new QueryBuilder())
            ->from('item as i')
            ->addLeftJoin('catalog_user as cu', 'i.catalog = cu.catalog')
            ->andWhere('cu.user = :userId')
            ->setParameter('userId', $userId)
            ->addOrderBy('i.id', 'DESC');

        if ($request->get('app-search')) {
            $query
                ->andWhere('(i.title LIKE :search1 OR i.url LIKE :search2 OR i.description LIKE :search3)')
                ->setParameter('search1', "%{$request->get('app-search')}%")
                ->setParameter('search2', "%{$request->get('app-search')}%")
                ->setParameter('search3', "%{$request->get('app-search')}%");
        }

        $pagination = (new PaginationQuery(
            $this->database,
            $query,
            Item::class,
            'i.*',
            'COUNT(i.id)',
            $limit,
            $page
        ))->getPagination();

        $this->loadTags($pagination->items);
        $this->loadCatalogs($pagination->items);

        return $pagination;
    }

    public function remove(Item $item): void
    {
        try {
            $this->database->transactionBegin();

            $this->database->query((new QueryBuilder())
                ->delete('item_tag')
                ->andWhere('item = :id')
                ->setParameter('id', $item->getId())
            );

            $this->database->query((new QueryBuilder())
                ->delete('item')
                ->andWhere('id = :id')
                ->setParameter('id', $item->getId())
            );

            $this->database->transactionCommit();
        } catch (Exception $exception) {
            $this->database->transactionRollback();
        }
    }

    /**
     * @param array|Item[] $items
     *
     * @throws Exception
     */
    private function loadTags(array $items): void
    {
        if (empty($items)) {
            return;
        }

        $ids = implode(', ', array_map(fn (Item $item) => (int) $item->getId(), $items));

        $tags = array_map(fn ($tag) => ItemTag::fromDatabase($tag), $this->database->query((new QueryBuilder())
            ->select('*')
            ->from('item_tag')
            ->andWhere("item IN ({$ids})")
        )->fetchAll());

        foreach ($items as $item) {
            $item->setTags(array_values(array_filter($tags, fn (ItemTag $tag) => $tag->getItem() === $item->getId())));
        }
    }

    /**
     * @param array|Item[] $items
     *
     * @throws Exception
     */
    private function loadCatalogs(array $items): void
    {
        if (empty($items)) {
            return;
        }

        $ids = implode(', ', array_map(fn (Item $item) => (int) $item->getCatalog(), $items));

        $catalogs = $this->database->query((new QueryBuilder())
            ->select('*')
            ->from('catalog')
            ->andWhere("id IN ({$ids})")
        )->fetchAll();

        $catalogMap = [];

        foreach ($catalogs as $catalog) {
            $catalogMap[$catalog['id']] = Catalog::fromDatabase($catalog);
        }

        foreach ($items as $item) {
            $item->setCatalogModel($catalogMap[$item->getCatalog()] ?? null);
        }
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
}
