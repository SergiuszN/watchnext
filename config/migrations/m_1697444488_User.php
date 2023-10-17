<?php

namespace Migrations;

use WatchNext\Engine\Database\Migration;

class m_1697444488_User extends Migration {
    public function up(): void {
        $this->connection->executeStatement(
            "CREATE TABLE `user` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `login` VARCHAR(60) NOT NULL,
                `password` CHAR(60) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT NOW(),
                `remember_me_key` CHAR(16) NULL,
                `remember_me_token` CHAR(60) NULL,
                `roles` TEXT NOT NULL,
                UNIQUE KEY(`login`) 
            )"
        );
    }

    public function down(): void {
        $this->connection->executeStatement(
            "DROP TABLE `user`"
        );
    }
}