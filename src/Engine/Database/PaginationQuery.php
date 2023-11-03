<?php

namespace WatchNext\Engine\Database;

use WatchNext\Engine\Template\PaginationCollection;

readonly class PaginationQuery
{
    public function __construct(
        private Database $database,
        private QueryBuilder $query,
        private string $class,
        private string $select,
        private string $selectCount,
        private int $limit,
        private int $page
    ) {
    }

    public function getPagination(): PaginationCollection
    {
        $countQuery = $this->query->select($this->selectCount);

        $count = (int) $this->database
            ->prepare($countQuery->getSql())
            ->execute($countQuery->getParams())
            ->fetchSingle();

        $query = (clone $countQuery)
            ->select($this->select)
            ->limit($this->limit, $this->limit * ($this->page - 1));

        $items = $this->database
            ->prepare($query->getSql())
            ->execute($query->getParams())
            ->fetchAll();

        return new PaginationCollection(
            $this->page,
            $this->limit,
            ceil($count / $this->limit),
            $count,
            array_map(fn ($item) => call_user_func($this->class . '::fromDatabase', $item), $items)
        );
    }
}
