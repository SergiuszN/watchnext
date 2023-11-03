<?php

namespace Unit\Engine\Database;

use Exception;
use PHPUnit\Framework\TestCase;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\QueryBuilder;

class DatabaseTest extends TestCase
{
    private Database $database;

    public function setUp(): void
    {
        $this->database = new Database('sqlite::memory:');
    }

    public function testGetDatabaseName(): void
    {
        self::assertEquals('', $this->database->getDatabase());

        $envDatabase = new Database();

        self::assertNotEquals('', $envDatabase->getDatabase());
    }

    public function testDebug(): void
    {
        $this->database->setIsDebug(false);
        self::assertFalse($this->database->isDebug());
        $this->database->setIsDebug(true);
        self::assertTrue($this->database->isDebug());
    }

    public function testLog(): void
    {
        $this->database->log('query', ['x' => 'y'], 100);
        $logs = $this->database->getLogs();
        self::assertEquals('query', $logs[0]['query']);
        self::assertEquals(['x' => 'y'], $logs[0]['params']);
        self::assertEquals(100, $logs[0]['time']);
    }

    public function testTransaction(): void
    {
        $this->database->setIsDebug(true);
        $this->database->execute('CREATE TABLE test_transaction(name VARCHAR(255));');
        $this->database->setIsDebug(false);

        $this->database->transactionBegin();
        $this->database->execute("INSERT INTO test_transaction VALUES ('firstname')");
        $this->database->transactionCommit();

        $count = $this->database->prepare('SELECT COUNT(*) FROM test_transaction')
            ->execute()
            ->fetchSingle();

        self::assertEquals(1, $count);

        try {
            $this->database->transactionBegin();
            $this->database->execute("INSERT INTO test_transaction VALUES ('firstname')");
            throw new Exception('blah');
            $this->database->transactionCommit();
        } catch (Exception $exception) {
            $this->database->transactionRollback();
        }

        $count = $this->database->query((new QueryBuilder())
            ->select('COUNT(*)')
            ->from('test_transaction')
        )->fetchSingle();

        self::assertEquals(1, $count);
    }

    public function testLastIdAndQuery(): void
    {
        $this->database->execute('CREATE TABLE test_id(id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255));');
        $this->database->execute("INSERT INTO test_id VALUES (null, 'test')");

        self::assertEquals(1, $this->database->getLastInsertId());
    }
}
