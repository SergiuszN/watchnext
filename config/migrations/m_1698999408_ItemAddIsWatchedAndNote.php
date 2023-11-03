<?php

namespace Migrations;

use WatchNext\Engine\Database\Migration;

class m_1698999408_ItemAddIsWatchedAndNote extends Migration
{
    public function up(): void
    {
        $this->database->execute(
            "ALTER TABLE `item` 
                 ADD COLUMN is_watched TINYINT(1) NOT NULL DEFAULT 0,
                 ADD COLUMN note TEXT NOT NULL DEFAULT ''
        ");
    }

    public function down(): void
    {
        $this->database->execute(
            'ALTER TABLE `item` 
                 DROP COLUMN is_watched,
                 DROP COLUMN note
        ');
    }
}
