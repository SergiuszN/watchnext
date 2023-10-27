<?php

namespace WatchNext\Engine\Database;

use PDO;

class Database {
    private static ?PDO $pdo = null;

    private static string $databaseName = '';

    private static array $debug = [];
    private static bool $isDebug;

    public function __construct() {
        if (self::$pdo === null) {
            self::$isDebug = $_ENV['APP_ENV'] === 'dev';
            self::$pdo = new PDO(
                $_ENV['DATABASE_DSN'],
                $_ENV['DATABASE_USER'] ?: null,
                $_ENV['DATABASE_PASSWORD'] ?: null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            preg_match('/dbname=([^;]*)/', $_ENV['DATABASE_DSN'], $matches);
            self::$databaseName = $matches[1] ?? '';
        }


        $sth = self::$pdo->prepare("SELECT * FROM user WHERE 1");
        $sth->execute();
        $sth->fetch();
        $sth->fetchAll();
    }

    public function getDatabase(): string {
        return self::$databaseName;
    }

    public function setIsDebug(bool $isDebug): void {
        self::$isDebug = $isDebug;
    }

    public function isDebug(): bool {
        return self::$isDebug;
    }

    public function log(string $query, array $params, float $time): void {
        self::$debug[] = ['query' => $query, 'params' => $params, 'time' => $time];
    }

    public function getLogs(): array {
        return self::$debug;
    }

    public function transactionBegin(): void {
        self::$pdo->beginTransaction();
    }

    public function transactionCommit(): void {
        self::$pdo->commit();
    }

    public function transactionRollback(): void {
        self::$pdo->rollBack();
    }

    public function prepare(string $sql): Statement {
        return new Statement($this, self::$pdo->prepare($sql));
    }

    public function execute(string $sql): void {
        self::$pdo->exec($sql);
    }

    public function getLastInsertId(): int|string|null {
        $id = self::$pdo->lastInsertId();

        if (is_numeric($id)) {
            return (int) $id;
        }

        if (is_string($id)) {
            return $id;
        }

        return null;
    }
}