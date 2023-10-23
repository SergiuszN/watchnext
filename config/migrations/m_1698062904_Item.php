<?php

namespace Migrations;

use WatchNext\Engine\Database\Migration;

class m_1698062904_Item extends Migration {
    public function up(): void {
        $this->connection->executeStatement(
            "CREATE TABLE `item` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `url` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `image` VARCHAR(255) NOT NULL,
                `owner` INT NOT NULL,
                `added_at` DATETIME NOT NULL DEFAULT NOW(),
                CONSTRAINT `fk_item_user` FOREIGN KEY (`owner`) REFERENCES `user`(`id`)
            )"
        );
    }

    public function down(): void {
        $this->connection->executeStatement(
            "DROP TABLE `item`"
        );
    }
}