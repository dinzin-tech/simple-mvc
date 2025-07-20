<?php

namespace Core;

class Config
{
    private static $settings = [];

    // constructor is private to prevent instantiation
    public function __construct() {
        // load from $_ENV or a config file
        self::$settings = [
            'base_url' => $_ENV['BASE_URL'] ?? 'http://localhost',
        ];

        // You can load more settings from a config file or environment variables here
        // For example, you can load from a .env file or a config.php file
        // self::$settings = parse_ini_file('config.ini');
        // or load from environment variables

        self::$settings = array_merge(self::$settings, $_ENV);
    }

    public static function set(string $key, $value): void
    {
        self::$settings[$key] = $value;
    }

    public static function get(string $key)
    {
        return self::$settings[$key] ?? null;
    }
}
