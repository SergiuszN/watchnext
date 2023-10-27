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
}