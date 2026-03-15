<?php
declare(strict_types=1);

namespace Core;

class QueryBuilder {
    protected Database $db;
    protected string $table = '';
    protected array $selects = ['*'];
    protected array $joins = [];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orderBys = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected ?string $rawSql = null;

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function table(string $table): self {
        $this->table = $table;
        return $this;
    }

    public function select(string ...$columns): self {
        $this->selects = empty($columns) ? ['*'] : $columns;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value = null): self {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $paramName = str_replace('.', '_', $column) . '_' . count($this->bindings);
        $this->wheres[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'param' => $paramName
        ];
        $this->bindings[$paramName] = $value;
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value = null): self {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $paramName = str_replace('.', '_', $column) . '_' . count($this->bindings);
        $this->wheres[] = [
            'type' => 'OR',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'param' => $paramName
        ];
        $this->bindings[$paramName] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self {
        return $this->addInCondition('AND', $column, $values);
    }

    public function orWhereIn(string $column, array $values): self {
        return $this->addInCondition('OR', $column, $values);
    }

    protected function addInCondition(string $type, string $column, array $values): self {
        if (empty($values)) {
            // If empty, we add a condition that always fails
            $this->wheres[] = [
                'type' => $type,
                'column' => '0',
                'operator' => '=',
                'value' => '1',
                'param' => null,
                'isIn' => false
            ];
            return $this;
        }

        $params = [];
        foreach ($values as $index => $value) {
            $paramName = str_replace('.', '_', $column) . '_in_' . count($this->bindings);
            $this->bindings[$paramName] = $value;
            $params[] = ":$paramName";
        }

        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'operator' => 'IN',
            'value' => $values,
            'params' => $params,
            'isIn' => true
        ];

        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self {
        $this->joins[] = "$type JOIN $table ON $first $operator $second";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function raw(string $sql, array $bindings = []): self {
        $this->rawSql = $sql;
        $this->bindings = array_merge($this->bindings, $bindings);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->orderBys[] = "$column " . strtoupper($direction);
        return $this;
    }

    public function limit(int $limit, ?int $offset = null): self {
        $this->limit = $limit;
        if ($offset !== null) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function get(): array {
        $sql = $this->toSql();
        return $this->db->fetchAll($sql, $this->bindings);
    }

    public function first(): array|false|null {
        $this->limit(1);
        $sql = $this->toSql();
        return $this->db->fetch($sql, $this->bindings);
    }

    public function insert(array $data): bool|\PDOStatement {
        if (empty($data)) {
            throw new \InvalidArgumentException("No data provided for insert.");
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($key) => ":ins_$key", array_keys($data)));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        
        $bindings = [];
        foreach ($data as $key => $value) {
            $bindings["ins_$key"] = $value;
        }

        return $this->db->query($sql, $bindings);
    }

    public function update(array $data): bool|\PDOStatement {
        if (empty($data)) {
            throw new \InvalidArgumentException("No data provided for update.");
        }

        $sets = [];
        $bindings = [];
        
        foreach ($data as $key => $value) {
            $param = "upd_$key";
            $sets[] = "$key = :$param";
            $bindings[$param] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        $sql .= $this->buildWhereClause();

        return $this->db->query($sql, array_merge($bindings, $this->bindings));
    }

    public function delete(): bool|\PDOStatement {
        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhereClause();

        return $this->db->query($sql, $this->bindings);
    }

    public function toSql(): string {
        if ($this->rawSql !== null) {
            return $this->rawSql;
        }

        $sql = "SELECT " . implode(', ', $this->selects) . " FROM {$this->table}";
        
        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }

        $sql .= $this->buildWhereClause();

        if (!empty($this->orderBys)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBys);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return $sql;
    }

    protected function buildWhereClause(): string {
        if (empty($this->wheres)) {
            return '';
        }

        $clause = ' WHERE ';
        foreach ($this->wheres as $index => $where) {
            if ($index > 0) {
                $clause .= " {$where['type']} ";
            }

            if (isset($where['isIn']) && $where['isIn']) {
                $placeholders = implode(', ', $where['params']);
                $clause .= "{$where['column']} IN ($placeholders)";
            } elseif (isset($where['param'])) {
                $clause .= "{$where['column']} {$where['operator']} :{$where['param']}";
            } else {
                // Fallback for empty IN or static conditions
                $clause .= "{$where['column']} {$where['operator']} {$where['value']}";
            }
        }
        
        return $clause;
    }
}
