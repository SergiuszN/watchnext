<?php

namespace WatchNext\Engine\Database;

class QueryBuilder
{
    private string $_select;
    private string $_from;
    private array $_joins;
    private array $_andWheres;
    private array $_groupBy;
    private string $_having;
    private array $_orderBy;
    private string $_limit;
    private array $_params;
    private string $_insert;
    private array $_values;
    private string $_update;
    private array $_sets;
    private string $_delete;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->_select = '';
        $this->_from = '';
        $this->_joins = [];
        $this->_andWheres = [];
        $this->_groupBy = [];
        $this->_having = '';
        $this->_orderBy = [];
        $this->_limit = '';
        $this->_params = [];

        $this->_insert = '';
        $this->_values = [];

        $this->_update = '';
        $this->_sets = [];

        $this->_delete = '';
    }

    public function select(string $select): self
    {
        $this->_select = "SELECT $select";

        return $this;
    }

    public function from(string $from): self
    {
        $this->_from = "FROM $from";

        return $this;
    }

    public function addJoin(string $type, string $join, string $on): self
    {
        $this->_joins[] = "$type JOIN $join ON $on";

        return $this;
    }

    public function addLeftJoin(string $join, string $on): self
    {
        return $this->addJoin('LEFT', $join, $on);
    }

    public function andWhere(string $where): self
    {
        $this->_andWheres[] = $where;

        return $this;
    }

    public function addGroupBy(string $groupBy): self
    {
        $this->_groupBy[] = $groupBy;

        return $this;
    }

    public function having(string $having): self
    {
        $this->_having = $having;

        return $this;
    }

    public function addOrderBy(string $orderBy, string $type): self
    {
        $this->_orderBy[] = "$orderBy $type";

        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        if ($offset > 0) {
            $this->_limit = "LIMIT $limit OFFSET $offset";
        } else {
            $this->_limit = "LIMIT $limit";
        }

        return $this;
    }

    public function setParameter(string $name, mixed $value): self
    {
        $this->_params[$name] = $value;

        return $this;
    }

    public function setParameters(array $parameters): self
    {
        $this->_params = array_merge($this->_params, $parameters);

        return $this;
    }

    public function getParams(): array
    {
        return $this->_params;
    }

    public function insert(string $insert): self
    {
        $this->_insert = "INSERT INTO $insert";

        return $this;
    }

    public function addValue(string $value, string $paramName = null): self
    {
        $this->_values[] = [$value, $paramName ? ':' . $paramName : ':' . str_replace('`', '', $value)];

        return $this;
    }

    public function update(string $update): self
    {
        $this->_update = "UPDATE $update";

        return $this;
    }

    public function addSet(string $set, string $paramName = null): self
    {
        $paramName = $paramName ? ':' . $paramName : ':' . str_replace('`', '', $set);
        $this->_sets[] = "$set = $paramName";

        return $this;
    }

    public function delete(string $delete, string $withFrom = ''): self
    {
        /** @noinspection SqlWithoutWhere */
        $this->_delete = !$withFrom ? "DELETE FROM $delete" : "DELETE $withFrom FROM $delete";

        return $this;
    }

    public function getSql(): string
    {
        if ($this->_select) {
            return $this->buildSelect();
        }

        if ($this->_insert) {
            return $this->buildInsert();
        }

        if ($this->_update) {
            return $this->buildUpdate();
        }

        if ($this->_delete) {
            return $this->buildDelete();
        }

        return '';
    }

    private function buildSelect(): string
    {
        $sql = $this->_select . "\n";
        $sql .= $this->_from . "\n";

        if (!empty($this->_joins)) {
            $sql .= implode("\n", $this->_joins) . "\n";
        }

        if (!empty($this->_andWheres)) {
            $sql .= 'WHERE ' . implode("\nAND ", $this->_andWheres) . "\n";
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

    private function buildInsert(): string
    {
        $sql = $this->_insert . " (\n";
        $sql .= implode(",\n", array_map(fn ($value) => $value[0], $this->_values));
        $sql .= "\n) VALUES (\n";
        $sql .= implode(",\n", array_map(fn ($value) => $value[1], $this->_values));
        $sql .= "\n)\n";

        return $sql;
    }

    private function buildUpdate(): string
    {
        $sql = $this->_update . "\n";
        $sql .= 'SET ' . implode(",\n", $this->_sets) . "\n";

        /** @noinspection DuplicatedCode */
        if (!empty($this->_andWheres)) {
            $sql .= 'WHERE ' . implode("\nAND ", $this->_andWheres) . "\n";
        }

        if (!empty($this->_orderBy)) {
            $sql .= 'ORDER BY ' . implode(', ', $this->_orderBy) . "\n";
        }

        if ($this->_limit) {
            $sql .= $this->_limit . "\n";
        }

        return $sql;
    }

    private function buildDelete(): string
    {
        $sql = $this->_delete . "\n";

        if (!empty($this->_joins)) {
            $sql .= implode("\n", $this->_joins) . "\n";
        }

        /** @noinspection DuplicatedCode */
        if (!empty($this->_andWheres)) {
            $sql .= 'WHERE ' . implode("\nAND ", $this->_andWheres) . "\n";
        }

        if (!empty($this->_orderBy)) {
            $sql .= 'ORDER BY ' . implode(', ', $this->_orderBy) . "\n";
        }

        if ($this->_limit) {
            $sql .= $this->_limit . "\n";
        }

        return $sql;
    }
}
