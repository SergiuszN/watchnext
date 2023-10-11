<?php

namespace WatchNext\Engine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Tools\DsnParser;

class Database {
    private static ?Connection $connection = null;

    /**
     * @throws Exception
     */
    public static function init(): void {
        if (self::$connection) {
            return;
        }

        $dsnParser = new DsnParser(['mysql' => 'pdo_mysql', 'postgres' => 'pdo_pgsql']);
        $connectionParams = $dsnParser->parse($_ENV['DATABASE_URL']);
        self::$connection = DriverManager::getConnection($connectionParams);
    }

    public static function getConnection(): Connection {
        return self::$connection;
    }
}