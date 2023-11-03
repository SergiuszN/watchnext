<?php

namespace Migrations;

use WatchNext\Engine\Database\Migration;

class m_1698085465_Catalog extends Migration
{
    public function up(): void
    {
        $this->database->execute(
            'CREATE TABLE `catalog` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `owner` INT NOT NULL,
                `shared` TINYINT(1) NOT NULL DEFAULT 0,
                `name` VARCHAR(255) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT NOW(),
                CONSTRAINT `fk_catalog_user` FOREIGN KEY (`owner`) REFERENCES `user`(`id`)
            )'
        );

        $this->database->execute(
            'CREATE TABLE `catalog_item` (
                `catalog` INT NOT NULL,
                `item` INT NOT NULL ,
                PRIMARY KEY (`catalog`, `item`),
                CONSTRAINT `fk_catalog_item_catalog` FOREIGN KEY (`catalog`) REFERENCES `catalog`(`id`),
                CONSTRAINT `fk_catalog_item_item` FOREIGN KEY (`item`) REFERENCES `item`(`id`)
            )'
        );
    }

    public function down(): void
    {
        $this->database->execute(
            'DROP TABLE `catalog`;'
        );

        $this->database->execute(
            'DROP TABLE `catalog_item`;'
        );
    }
}
