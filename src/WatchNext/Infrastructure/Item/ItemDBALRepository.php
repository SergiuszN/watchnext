<?php

namespace WatchNext\WatchNext\Infrastructure\Item;

use Doctrine\DBAL\Connection;
use WatchNext\Engine\Database\Database;
use WatchNext\WatchNext\Domain\Item\Item;
use WatchNext\WatchNext\Domain\Item\ItemRepository;

class ItemDBALRepository implements ItemRepository {
    private Connection $connection;

    public function __construct(Database $database) {
        $this->connection = $database->getConnection();
    }

    public function save(Item $item): void {
        if ($item->getId() === null) {
            $this->connection->prepare("
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
            ")->executeStatement($item->toDatabase());
            $item->setId($this->connection->lastInsertId());
        } else {
            $this->connection->prepare("
                UPDATE `item` SET
                    `title` = :title,
                    `url` = :url,
                    `description` = :description,
                    `image` = :image,
                    `owner` = :owner,
                    `added_at` = :added_at
                WHERE `id` = :id
            ")->executeStatement(array_merge($item->toDatabase(), ['id' => $item->getId()]));
        }
    }

    public function find(int $id): ?Item {
        $data = $this->connection->prepare("SELECT * FROM `item` WHERE id=:id")
            ->executeQuery(['id' => $id])
            ->fetchAssociative();

        return $data ? Item::fromDatabase($data) : null;
    }

    public function findAllForUser(?int $owner) {
        $data = $this->connection->prepare("SELECT * FROM `item` WHERE owner=:owner ORDER BY id DESC")
            ->executeQuery(['owner' => $owner])
            ->fetchAllAssociative();

        return array_map(fn (array $item) => Item::fromDatabase($item), $data);
    }
}