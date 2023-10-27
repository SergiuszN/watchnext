<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use Exception;
use WatchNext\WatchNext\Domain\Catalog\Catalog;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;

class CatalogPDORepository extends PDORepository implements CatalogRepository {
    public function save(Catalog $catalog): void {
        if ($catalog->getId() === null) {
            $this->database->prepare("
                INSERT INTO `catalog` (
                    `owner`, 
                    `shared`,
                    `default`,
                    `name`,
                    `created_at` 
                )
                VALUES (
                    :owner, 
                    :shared,
                    :default,
                    :name,
                    :created_at 
                )
            ")->execute($catalog->toDatabase());
            $catalog->setId($this->database->getLastInsertId());
        } else {
            $this->database->prepare("
                UPDATE `catalog` SET
                    `owner` = :title,
                    `shared` = :url,
                    `default` = :description,
                    `name` = :image,
                    `created_at` = :owner
                WHERE `id` = :id
            ")->execute(array_merge($catalog->toDatabase(), ['id' => $catalog->getId()]));
        }
    }

    /**
     * @throws Exception
     */
    public function find(int $catalogId): ?Catalog {
        $data = $this->database->prepare("SELECT * FROM `catalog` WHERE id=:id LIMIT 1")
            ->execute(['id' => $catalogId])
            ->fetch();

        return $data ? Catalog::fromDatabase($data) : null;
    }

    /**
     * @throws Exception
     */
    public function findDefaultForUser(?int $ownerId): ?Catalog {
        $data = $this->database->prepare("SELECT * FROM `catalog` WHERE owner=:id AND `default`=1 LIMIT 1")
            ->execute(['id' => $ownerId])
            ->fetch();

        return $data ? Catalog::fromDatabase($data) : null;
    }

    /**
     * @throws Exception
     */
    public function findAllForUser(?int $userId): array {
        $data = $this->database->prepare("SELECT * FROM `catalog` WHERE owner=:id")
            ->execute(['id' => $userId])
            ->fetchAll();

        return array_map(fn ($r) => Catalog::fromDatabase($r), $data);
    }
}