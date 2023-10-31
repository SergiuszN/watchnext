<?php

namespace WatchNext\Engine\Database;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    private static string $databaseName = '';

    private static array $debug = [];
    private static bool $isDebug;

    public function __construct()
    {
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
    }

    public function getDatabase(): string
    {
        return self::$databaseName;
    }

    public function setIsDebug(bool $isDebug): void
    {
        self::$isDebug = $isDebug;
    }

    public function isDebug(): bool
    {
        return self::$isDebug;
    }

    public function log(string $query, array $params, float $time): void
    {
        self::$debug[] = ['query' => $query, 'params' => $params, 'time' => $time];
    }

    public function getLogs(): array
    {
        return self::$debug;
    }

    public function transactionBegin(): void
    {
        self::$pdo->beginTransaction();
    }

    public function transactionCommit(): void
    {
        self::$pdo->commit();
    }

    public function transactionRollback(): void
    {
        self::$pdo->rollBack();
    }

    public function prepare(string $sql): Statement
    {
        return new Statement($this, self::$pdo->prepare($sql));
    }

    public function execute(string $sql): void
    {
        $tick = $this->isDebug() ? microtime(true) : 0;

        self::$pdo->exec($sql);

        if ($this->isDebug()) {
            $this->log($sql, [], microtime(true) - $tick);
        }
    }

    public function query(QueryBuilder $query): Statement
    {
        return $this
            ->prepare($query->getSql())
            ->execute($query->getParams());
    }

    public function getLastInsertId(): int|null
    {
        $id = self::$pdo->lastInsertId();

        return $id ? (int) $id : null;
    }
}
