<?php

namespace Core;

use ReflectionClass;
use ReflectionProperty;

abstract class Model {
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
        
        if (!$this->table) {
            $this->table = strtolower((new ReflectionClass($this))->getShortName()) . 's';
        }
    }

    public function hydrate(array $data): void {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function find(int $id): ?static {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1";
        $data = $instance->db->fetch($sql, ['id' => $id]);

        if ($data) {
            $instance->hydrate($data);
            return $instance;
        }
        return null;
    }

    public static function findOneBy(array $conditions): ?static {
        $results = self::findBy($conditions, 1);
        return $results ? $results[0] : null;
    }

    public static function findBy(array $conditions = [], int $limit = 0, string $orderBy = ''): array {
        $instance = new static();
        $whereClause = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $whereClause[] = "$key = :$key";
            $params[$key] = $value;
        }

        $sql = "SELECT * FROM {$instance->table}";

        if ($whereClause) {
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }

        $rows = $instance->db->fetchAll($sql, $params);
        return array_map(fn($row) => (new static())->hydrate($row), $rows);
    }

    public static function findAll(): array {
        return self::findBy();
    }

    public function save(): static {
        $properties = (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC);
        $data = [];

        foreach ($properties as $property) {
            $data[$property->getName()] = $this->{$property->getName()};
        }

        return !empty($this->{$this->primaryKey}) ? $this->update($data) : $this->create($data);
    }

    private function create(array $data): static {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        
        $this->db->query($sql, $data);
        $this->{$this->primaryKey} = $this->db->lastInsertId();
        
        return $this;
    }

    private function update(array $data): static {
        $fields = [];
        foreach ($data as $key => $value) {
            if ($key !== $this->primaryKey) {
                $fields[] = "$key = :$key";
            }
        }

        $sql = "UPDATE {$this->table} SET " . implode(", ", $fields) . " WHERE {$this->primaryKey} = :{$this->primaryKey}";
        $this->db->query($sql, $data);
        
        return $this;
    }

    public function delete(): bool {
        if (empty($this->{$this->primaryKey})) {
            return false;
        }

        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return (bool) $this->db->query($sql, ['id' => $this->{$this->primaryKey}]);
    }

    public static function count(array $conditions = []): int {
        $instance = new static();
        $whereClause = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $whereClause[] = "$key = :$key";
            $params[$key] = $value;
        }

        $sql = "SELECT COUNT(*) FROM {$instance->table}";

        if ($whereClause) {
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        return (int) $instance->db->fetch($sql, $params)['COUNT(*)'];
    }
}
