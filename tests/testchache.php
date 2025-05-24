<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Phpfastcache\Drivers\Files\Config;
use Phpfastcache\CacheManager;

// Custom configuration for Files driver
/*$config = new Config([
    'path' => '/custom_cache',  // Set custom cache directory
    // 'defaultTtl' => 1800, // Cache expiration time (30 minutes)
    // 'securityKey' => 'my_secure_key', // Set a custom security key
    // 'compress_data' => true, // Enable data compression
    // 'cacheFileExtension' => '.cache', // Use .cache extension instead of .txt
    // 'cacheFileExtensionFallback' => true, // Use .cache extension as a fallback
    'preventCacheSlams' => true, // Prevent cache slamming
    'cacheSlamsTimeout' => 60, // Set cache slam timeout to 60 seconds
    'secureFileManipulation' => true, // Secure file manipulation
    'cacheFileExtension' => 'cache', // Use .cache extension for cache files
]);

// CacheManager::setDefaultConfig($config);

// Initialize PHPFastCache with the Files driver and custom settings
$cache = CacheManager::getInstance('Files', $config);


// get the cache instance

$cache_instance = $cache->getItem('my_cache_instance');
if (is_null($cache_instance->get())) {
    // Cache instance not found, create a new one
    $cache_instance->set('my_cache_instance', 'my_value')->expiresAfter(3600); // Cache for 1 hour
    $cache->save($cache_instance);
} else {
    // Cache instance found, retrieve the value
    $value = $cache_instance->get('my_cache_instance');
    echo "Cache instance value: " . $value . PHP_EOL; // Output: Cache instance value: my_value
}*/

$cache = new \Core\Cache('cache');

$cache->set('my_key', 'my_value', 3600); // Cache for 1 hour

$value = $cache->get('my_key');

echo "Cache value: " . $value . PHP_EOL; // Output: Cache value: my_value





