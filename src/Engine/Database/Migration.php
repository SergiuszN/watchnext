<?php

namespace WatchNext\Engine\Database;

use WatchNext\Engine\Container;

abstract class Migration
{
    public function __construct(
        protected Container $container,
        protected Database $database
    ) {
    }

    public function up(): void
    {
    }

    public function down(): void
    {
    }
}
