<?php

namespace WatchNext\Engine\Database;

use PDOStatement;

readonly class Statement
{
    public function __construct(private Database $database, private PDOStatement $PDOStatement)
    {
    }

    public function execute(array $params = []): self
    {
        $tick = $this->database->isDebug() ? microtime(true) : 0;
        $this->PDOStatement->execute($params);

        if ($this->database->isDebug()) {
            $this->database->log($this->PDOStatement->queryString, $params, microtime(true) - $tick);
        }

        return $this;
    }

    public function fetch(): ?array
    {
        return $this->PDOStatement->fetch() ?: null;
    }

    public function fetchAll(): array
    {
        return $this->PDOStatement->fetchAll() ?: [];
    }

    public function fetchSingle(): mixed
    {
        $row = $this->PDOStatement->fetch();

        if (empty($row)) {
            return null;
        }

        return reset($row);
    }
}
