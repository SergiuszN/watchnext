<?php

namespace Migrations;

use WatchNext\Engine\Database\Migration;

class m_1699264533_ItemTag extends Migration
{
    public function up(): void
    {
        $this->database->execute(
            'CREATE TABLE `item_tag` (
                `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `item` INT NOT NULL,
                `value` VARCHAR(120) NULL,
                `created_at` DATETIME NOT NULL DEFAULT NOW(),
                CONSTRAINT `fk_item_tag_item` FOREIGN KEY (`item`) REFERENCES `item`(`id`),
                INDEX `i_item_tag_item_value` (`item`, `value`)
            )'
        );
    }

    public function down(): void
    {
        $this->database->execute(
            'DROP TABLE `item_tag`;'
        );
    }
}
