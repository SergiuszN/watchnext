<?php

namespace WatchNext\Engine\Database;

use PDO;

class Database
{
    private ?PDO $connection;

    private string $databaseName;

    private static array $debug = [];
    private static bool $isDebug;

    public function __construct(string $dsn = null, string $user = null, string $password = null)
    {
        if ($dsn === null) {
            $dsn = $_ENV['DATABASE_DSN'];
            $user = $_ENV['DATABASE_USER'];
            $password = $_ENV['DATABASE_PASSWORD'];
        }

        self::$isDebug = $_ENV['APP_ENV'] === 'dev';
        $this->connection = new PDO(
            $dsn,
            $user ?: null,
            $password ?: null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        preg_match('/dbname=([^;]*)/', $dsn, $matches);
        $this->databaseName = $matches[1] ?? '';
    }

    public function getDatabase(): string
    {
        return $this->databaseName;
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
        $this->connection->beginTransaction();
    }

    public function transactionCommit(): void
    {
        $this->connection->commit();
    }

    public function transactionRollback(): void
    {
        $this->connection->rollBack();
    }

    public function prepare(string $sql): Statement
    {
        return new Statement($this, $this->connection->prepare($sql));
    }

    public function execute(string $sql): void
    {
        $tick = $this->isDebug() ? microtime(true) : 0;

        $this->connection->exec($sql);

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
        $id = $this->connection->lastInsertId();

        return $id ? (int) $id : null;
    }
}
