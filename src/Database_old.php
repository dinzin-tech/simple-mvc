<?php
declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;
    private $connection;
    protected static $queries = [];

    private function __construct() {
         // Retrieve configuration from environment variables
        $host     = $_ENV['DB_HOST'];
        $dbname   = $_ENV['DB_DATABASE'];
        $user     = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];

        // Create the PDO connection
        $this->connection = new PDO("mysql:host={$host};dbname={$dbname}", $user, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    public static function getQueries()
    {
        return self::$queries;
    }

    public function query($sql, $params = []) {
        $start = microtime(true);

        try {
            // Execute your query here using PDO
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            // Log the error
            error_log('Database query error: ' . $e->getMessage());
            // Optionally, rethrow the exception or handle it as needed
            // throw new Exception('Database query error', 0, $e);
        }

        $end = microtime(true);

        self::$queries[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => round(($end - $start) * 1000, 2)
        ];

        return $stmt;
    }
}