<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use WatchNext\Engine\Database\Database;

class PDORepository
{
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }
}
