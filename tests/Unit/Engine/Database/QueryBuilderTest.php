<?php

namespace Unit\Engine\Database;

use PHPUnit\Framework\TestCase;
use WatchNext\Engine\Database\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    public function testSelect(): void
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from('user as u')
            ->addLeftJoin('post as p', 'p.user = u.id')
            ->andWhere('u.status = :status')
            ->setParameters(['status' => 1])
            ->andWhere('p.name LIKE :search')
            ->setParameter('search', '%name%')
            ->addGroupBy('p.status')
            ->having('p.status > 1')
            ->addOrderBy('p.created', 'DESC')
            ->limit(1, 10);

        /** @noinspection SqlResolve */
        /** @noinspection SqlAggregates */
        self::assertEquals('SELECT *
FROM user as u
LEFT JOIN post as p ON p.user = u.id
WHERE u.status = :status
AND p.name LIKE :search
GROUP BY p.status
HAVING p.status > 1
ORDER BY p.created DESC
LIMIT 1 OFFSET 10
', $builder->getSql());
        self::assertEquals(['status' => 1, 'search' => '%name%'], $builder->getParams());
    }

    public function testUpdate(): void
    {
        $builder = new QueryBuilder();
        $builder
            ->update('user')
            ->addSet('role')
            ->addSet('status')
            ->setParameter('role', 'ROLE_USER')
            ->andWhere('name LIKE :search')
            ->setParameter('search', '%name%')
            ->setParameter('status', 1)
            ->having('status > 1')
            ->addOrderBy('created', 'DESC')
            ->limit(1);

        /** @noinspection SqlResolve */
        /** @noinspection SqlAggregates */
        self::assertEquals('UPDATE user
SET role = :role,
status = :status
WHERE name LIKE :search
ORDER BY created DESC
LIMIT 1
', $builder->getSql());
        self::assertEquals(['status' => 1, 'search' => '%name%', 'role' => 'ROLE_USER'], $builder->getParams());
    }

    public function testInsert(): void
    {
        $builder = new QueryBuilder();
        $builder
            ->insert('user')
            ->addValue('username')
            ->addValue('password')
            ->addValue('createdAt')
            ->setParameters(['username' => 'a', 'password' => 'b', 'createdAt' => 'c']);

        /** @noinspection SqlResolve */
        /** @noinspection SqlAggregates */
        self::assertEquals('INSERT INTO user (
username,
password,
createdAt
) VALUES (
:username,
:password,
:createdAt
)
', $builder->getSql());
        self::assertEquals(['username' => 'a', 'password' => 'b', 'createdAt' => 'c'], $builder->getParams());
    }

    public function testDelete(): void
    {
        $builder = new QueryBuilder();
        $builder
            ->delete('user')
            ->andWhere('name LIKE :search')
            ->setParameter('search', '%name%')
            ->addOrderBy('created', 'DESC')
            ->limit(1);

        /** @noinspection SqlResolve */
        /** @noinspection SqlAggregates */
        self::assertEquals('DELETE FROM user
WHERE name LIKE :search
ORDER BY created DESC
LIMIT 1
', $builder->getSql());
        self::assertEquals(['search' => '%name%'], $builder->getParams());
    }

    public function testClearAndEmptySql(): void
    {
        $builder = (new QueryBuilder())
            ->select('test');

        $builder->reset();

        self::assertEquals('', $builder->getSql());
    }
}
