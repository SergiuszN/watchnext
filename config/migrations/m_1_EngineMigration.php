<?php

namespace Migrations;

use WatchNext\Engine\Database\Migration;

class m_1_EngineMigration extends Migration
{
    public function up(): void
    {
        $this->database->execute('CREATE TABLE `migration` (
                version INT,
                name VARCHAR(255),
                executed_at DATETIME DEFAULT NOW()
        );');

        $this->database->execute('CREATE TABLE `user` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `login` VARCHAR(60) NOT NULL,
                `password` CHAR(60) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT NOW(),
                `remember_me_key` CHAR(16) NULL,
                `remember_me_token` CHAR(60) NULL,
                `roles` TEXT NOT NULL,
                UNIQUE KEY(`login`) 
        )');

        $this->database->execute('CREATE TABLE `command_bus` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `message_class` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `status` INT NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT NOW(),
                `created_by` INT NULL,
                CONSTRAINT `fk_command_bus_user` FOREIGN KEY (`created_by`) REFERENCES `user`(`id`)
        )');
    }

    public function down(): void
    {
        $this->database->execute('DROP TABLE `user`');
        $this->database->execute('DROP TABLE `migration`');
        $this->database->execute('DROP TABLE `command_bus`');
    }
}
