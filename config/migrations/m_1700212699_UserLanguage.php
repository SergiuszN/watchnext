<?php

namespace Migrations;

use WatchNext\Engine\Database\Migration;

class m_1700212699_UserLanguage extends Migration
{
    public function up(): void
    {
        $this->database->execute(
            'ALTER TABLE user ADD language varchar(10) NOT NULL AFTER password;'
        );
    }

    public function down(): void
    {
        $this->database->execute(
            'ALTER TABLE user DROP COLUMN language;'
        );
    }
}
