<?php

namespace WatchNext\Engine\Database;

use Doctrine\DBAL\Connection;
use WatchNext\Engine\Container;

abstract class Migration {
    public function __construct(
        protected Container  $container,
        protected Connection $connection
    ) {
    }

    public function up(): void {

    }

    public function down(): void {

    }
}