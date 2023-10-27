<?php

namespace WatchNext\Engine\Database;

class QueryBuilder {
    private string $_select;
    private string $_from;
    private array $_joins;
    private array $_andWheres;
    private array $_groupBy;
    private string $_having;
    private array $_orderBy;
    private string $_limit;
    private array $_params;


    public function __construct() {
        $this->reset();
    }

    public function reset(): void {
        $this->_select = '';
        $this->_from = '';
        $this->_joins = [];
        $this->_andWheres = [];
        $this->_groupBy = [];
        $this->_having = '';
        $this->_orderBy = [];
        $this->_limit = '';
        $this->_params = [];
    }

    public function select(string $select): self {
        $this->_select = "SELECT $select";
        return $this;
    }

    public function from(string $from): self {
        $this->_from = "FROM $from";
        return $this;
    }

    public function addJoin(string $type, string $join, string $on): self {
        $this->_joins[] = "$type JOIN $join ON $on";
        return $this;
    }

    public function addLeftJoin(string $join, string $on): self {
        return $this->addJoin('LEFT', $join, $on);
    }

    public function andWhere(string $where): self {
        $this->_andWheres[] = $where;
        return $this;
    }

    public function addGroupBy(string $groupBy): self {
        $this->_groupBy[] = $groupBy;
        return $this;
    }

    public function having(string $having): self {
        $this->_having = "HAVING $having";
        return $this;
    }

    public function addOrderBy(string $orderBy, string $type): self {
        $this->_orderBy[] = "$orderBy $type";
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self {
        if ($offset > 0) {
            $this->_limit = "LIMIT $limit OFFSET $offset";
        } else {
            $this->_limit = "LIMIT $limit";
        }

        return $this;
    }

    public function setParameter(string $name, mixed $value): self {
        $this->_params[$name] = $value;
        return $this;
    }

    public function getSql(): string {
        $sql = $this->_select . "\n";
        $sql .= $this->_from . "\n";

        if (!empty($this->_joins)) {
            $sql .= implode("\n", $this->_joins) . "\n";
        }

        if (!empty($this->_andWheres)) {
            $sql .= 'WHERE ' . implode("\n AND ", $this->_andWheres) . "\n";
        }

        if (!empty($this->_groupBy)) {
            $sql .= 'GROUP BY ' . implode(', ', $this->_groupBy) . "\n";
        }

        if ($this->_having) {
            $sql .= "HAVING {$this->_having}\n";
        }

        if (!empty($this->_orderBy)) {
            $sql .= 'ORDER BY ' . implode(', ', $this->_orderBy) . "\n";
        }

        if ($this->_limit) {
            $sql .= $this->_limit . "\n";
        }

        return $sql;
    }

    public function getParams(): array {
        return $this->_params;
    }
}