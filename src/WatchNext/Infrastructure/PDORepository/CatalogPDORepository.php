<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use Exception;
use WatchNext\Engine\Cache\CacheInterface;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\QueryBuilder;
use WatchNext\WatchNext\Domain\Catalog\Catalog;
use WatchNext\WatchNext\Domain\Catalog\CatalogItem;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\Catalog\CatalogUser;

class CatalogPDORepository extends PDORepository implements CatalogRepository {
    private CacheInterface $cache;

    public function __construct(Database $database, CacheInterface $cache) {
        parent::__construct($database);
        $this->cache = $cache;
    }

    public function save(Catalog $catalog): void {
        if ($catalog->getId() === null) {
            $this->database->query((new QueryBuilder())
                ->insert('`catalog`')
                ->addValue('`owner`')
                ->addValue('`shared`')
                ->addValue('`name`')
                ->addValue('`created_at`')
                ->setParameters($catalog->toDatabase())
            );
            $catalog->setId($this->database->getLastInsertId());
        } else {
            $this->database->query((new QueryBuilder())
                ->update('`catalog`')
                ->addSet('`owner`')
                ->addSet('`shared`')
                ->addSet('`name`')
                ->addSet('`created_at`')
                ->setParameters($catalog->toDatabase())
                ->andWhere('id = :id')
                ->setParameter('id', $catalog->getId())
            );
        }
    }

    /**
     * @throws Exception
     */
    public function find(int $catalogId): ?Catalog {
        $data = $this->database->query((new QueryBuilder())
            ->select('*')
            ->from('`catalog`')
            ->andWhere('id = :id')
            ->setParameter('id', $catalogId)
            ->limit(1)
        )->fetch();

        return $data ? Catalog::fromDatabase($data) : null;
    }

    /**
     * @throws Exception
     */
    public function findDefaultForUser(?int $userId): ?Catalog {
        return $this->cache->get("catalog.repo.findDefaultForUser.$userId", function () use ($userId) {
            $data = $this->database->query((new QueryBuilder())
                ->select('c.*')
                ->from('`catalog` as c')
                ->addLeftJoin('`catalog_user` as cu', 'cu.catalog = c.id')
                ->andWhere('cu.is_default = 1')
                ->andWhere('cu.user = :user')
                ->setParameter('user', $userId)
                ->limit(1)
            )->fetch();

            return $data ? Catalog::fromDatabase($data) : null;
        });
    }

    /**
     * @throws Exception
     */
    public function findAllForUser(?int $userId): array {
        $data = $this->database->query((new QueryBuilder())
            ->select('c.*')
            ->from('`catalog` as c')
            ->addLeftJoin('`catalog_user` as cu', 'cu.catalog = c.id')
            ->andWhere('cu.user = :user')
            ->setParameter('user', $userId)
        )->fetchAll();

        return array_map(fn($r) => Catalog::fromDatabase($r), $data);
    }

    public function hasAccess(CatalogUser $catalogUser): bool {
        return $this->cache->get("catalog.repo.hasAccess.{$catalogUser->catalog}.{$catalogUser->user}", function () use ($catalogUser) {
            return $this->database->query((new QueryBuilder())
                    ->select('COUNT(*)')
                    ->from('`catalog_user`')
                    ->andWhere('user = :user')
                    ->andWhere('catalog = :catalog')
                    ->setParameters($catalogUser->toDatabase())
                    ->limit(1)
                )->fetchSingle() > 0;
        });
    }

    public function addAccess(CatalogUser $catalogUser): void {
        $this->database->query((new QueryBuilder())
            ->insert('`catalog_user`')
            ->addValue('catalog')
            ->addValue('user')
            ->setParameters($catalogUser->toDatabase())
        );

        $this->cache->set("catalog.repo.hasAccess.{$catalogUser->catalog}.{$catalogUser->user}", true);
    }

    public function removeAccess(CatalogUser $catalogUser): ?int {
        $doesDefault = $this->database->query((new QueryBuilder())
                ->select('COUNT(*)')
                ->from('`catalog_user`')
                ->andWhere('catalog = :catalog')
                ->andWhere('user = :user')
                ->andWhere('id_default = 1')
                ->limit(1)
                ->setParameters($catalogUser->toDatabase())
            )->fetchSingle() > 0;

        $this->database->query((new QueryBuilder())
            ->delete('`catalog_user`')
            ->andWhere('catalog = :catalog')
            ->andWhere('user = :user')
            ->limit(1)
            ->setParameters($catalogUser->toDatabase())
        );

        $this->cache->set("catalog.repo.hasAccess.{$catalogUser->catalog}.{$catalogUser->user}", false);

        return $doesDefault ? (int) $catalogUser->user : null;
    }

    public function addItem(CatalogItem $catalogItem): void {
        $this->database->query((new QueryBuilder())
            ->insert('`catalog_item`')
            ->addValue('catalog')
            ->addValue('item')
            ->setParameters($catalogItem->toDatabase())
        );
    }

    public function setAsDefault(CatalogUser $catalogUser): void {
        $this->database->query((new QueryBuilder())
            ->update('`catalog_user`')
            ->addSet('is_default')
            ->setParameter('is_default', 0)
            ->andWhere('user = :user')
            ->setParameter('user', $catalogUser->user)
        );

        $this->database->query((new QueryBuilder())
            ->update('`catalog_user`')
            ->addSet('is_default')
            ->setParameter('is_default', 1)
            ->andWhere('catalog = :catalog')
            ->andWhere('user = :user')
            ->limit(1)
            ->setParameters($catalogUser->toDatabase())
        );

        $this->cache->delete("catalog.repo.findDefaultForUser.{$catalogUser->user}");
    }

    public function remove(Catalog $catalog): array {
        $this->database->transactionBegin();

        try {
            $this->database->query((new QueryBuilder())
                ->delete('item')
                ->andWhere('id IN (SELECT item FROM catalog_item WHERE catalog=:catalog)')
                ->setParameter('catalog', $catalog->getId())
            );

            $this->database->query((new QueryBuilder())
                ->delete('catalog_item')
                ->andWhere('catalog=:catalog')
                ->setParameter('catalog', $catalog->getId())
            );

            /** @var CatalogUser[] $removedDefaultCatalogsUsers */
            $removedDefaultCatalogsUsers = array_map(
                fn($row) => CatalogUser::fromDatabase($row),
                $this->database->query((new QueryBuilder())
                    ->select('*')
                    ->from('catalog_user')
                    ->andWhere('catalog=:catalog')
                    ->andWhere('is_default=1')
                    ->setParameter('catalog', $catalog->getId())
                )->fetchAll()
            );

            foreach ($removedDefaultCatalogsUsers as $catalogUser) {
                $this->cache->delete("catalog.repo.findDefaultForUser.{$catalogUser->user}");
            }

            $this->database->query((new QueryBuilder())
                ->delete('catalog_user')
                ->andWhere('catalog=:catalog')
                ->setParameter('catalog', $catalog->getId())
            );

            $this->database->query((new QueryBuilder())
                ->delete('catalog')
                ->andWhere('id=:id')
                ->setParameter('id', $catalog->getId())
            );

            $this->database->transactionCommit();
            return $removedDefaultCatalogsUsers;
        } catch (Exception $exception) {
            $this->database->transactionRollback();
            throw new $exception;
        }
    }
}