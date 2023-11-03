<?php

namespace Unit\Engine\Database;

use Monolog\Test\TestCase;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\QueryBuilder;

class StatementTest extends TestCase
{
    public function testFetch(): void
    {
        $database = new Database('sqlite::memory:');
        $database->setIsDebug(true);
        $database->execute('CREATE TABLE user(id INT, name varchar(255));');
        $database->execute("INSERT INTO user VALUES (1, 'first')");
        $database->execute("INSERT INTO user VALUES (1, 'second')");
        $database->execute("INSERT INTO user VALUES (1, 'third')");

        $allUsers = $database->query((new QueryBuilder())
            ->select('*')
            ->from('user')
        )->fetchAll();

        self::assertCount(3, $allUsers);

        $query = $database->query((new QueryBuilder())
            ->select('*')
            ->from('user')
        );

        $oneRow = $query->fetch();

        self::assertEquals(['id' => 1, 'name' => 'first'], $oneRow);

        $query = $database->query((new QueryBuilder())
            ->select('COUNT(*)')
            ->from('user')
        );

        self::assertEquals('3', $query->fetchSingle());
        self::assertEquals(null, $query->fetchSingle());
    }
}
