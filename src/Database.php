<?php
declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use Core\Logger;

class Database {
    private static array $instances = [];
    private PDO $connection;
    protected static array $queries = [];

    private function __construct(string $name) {
        try {
            $host     = $_ENV[strtoupper($name) . '_DB_HOST'];
            $dbname   = $_ENV[strtoupper($name) . '_DB_DATABASE'];
            $user     = $_ENV[strtoupper($name) . '_DB_USER'];
            $password = $_ENV[strtoupper($name) . '_DB_PASSWORD'];

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ];

            $this->connection = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());

            die('Database connection error. Check logs for details.');
        }
    }

    public static function getInstance(string $name = 'DEFAULT'): self {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }
        return self::$instances[$name];
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function query(string $sql, array $params = []): bool|\PDOStatement {
        $start = microtime(true);
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Database query [' . $sql . '] error: ' . $e->getMessage());
            return false;
        }

        $end = microtime(true);
        self::$queries[] = ['sql' => $sql, 'params' => $params, 'time' => round(($end - $start) * 1000, 2)];

        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }

    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }

    public static function getQueries(): array {
        return self::$queries;
    }
}
