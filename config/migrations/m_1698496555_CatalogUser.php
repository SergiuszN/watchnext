<?php

namespace Migrations;

use WatchNext\Engine\Database\Migration;

class m_1698496555_CatalogUser extends Migration {
    public function up(): void {
        $this->database->execute(
            "CREATE TABLE `catalog_user` (
                `catalog` INT NOT NULL,
                `user` INT NOT NULL ,
                `is_default` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`catalog`, `user`),
                CONSTRAINT `fk_catalog_user_catalog` FOREIGN KEY (`catalog`) REFERENCES `catalog`(`id`),
                CONSTRAINT `fk_catalog_user_user` FOREIGN KEY (`user`) REFERENCES `user`(`id`)
            )"
        );
    }

    public function down(): void {
        $this->database->execute(
            "DROP TABLE `catalog_user`;"
        );
    }
}