<?php

// Load Composer's autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define base path for the application if not defined yet
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load .env if it exists
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

// Optionally, you might want to switch the DB connection to a test database
// by overriding $_ENV values here, e.g.:
// $_ENV['DEFAULT_DB_DATABASE'] = $_ENV['DEFAULT_DB_DATABASE'] ?? 'test_db';
// $_ENV['DEFAULT_DB_HOST'] = $_ENV['DEFAULT_DB_HOST'] ?? '127.0.0.1';
// $_ENV['DEFAULT_DB_USER'] = $_ENV['DEFAULT_DB_USER'] ?? 'root';
// $_ENV['DEFAULT_DB_PASSWORD'] = $_ENV['DEFAULT_DB_PASSWORD'] ?? '';
