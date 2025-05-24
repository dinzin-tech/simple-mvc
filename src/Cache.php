<?php

declare(strict_types=1);

namespace Core;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Files\Config;

class Cache
{
    private $cacheInstance;
    protected $cache;
    

    public function __construct($path = BASE_PATH . '/storage/cache')
    {

        $this->cacheInstance = CacheManager::getInstance('Files', new Config([
            'path' => $path, // Set custom cache directory
            'defaultTtl' => 1800, // Cache expiration time (30 minutes)
            'securityKey' => 'my_secure_key', // Set a custom security key
            'cacheFileExtension' => 'cache', // Use .cache extension instead of .txt
            'preventCacheSlams' => true, // Prevent cache slamming
            'cacheSlamsTimeout' => 60, // Set cache slam timeout to 60 seconds
            'secureFileManipulation' => true, // Secure file manipulation
        ]));

        $this->cache = $this->cacheInstance;
    }

    public function getCacheInstance()
    {
        return $this->cacheInstance;
    }

    public function setCacheInstance($cacheInstance)
    {
        $this->cacheInstance = $cacheInstance;
    }

    public function get(string $key)
    {
        return $this->cacheInstance->getItem($key)->get();
    }

    public function set(string $key, $value, int $ttl = 3600)
    {
        $item = $this->cacheInstance->getItem($key);
        $item->set($value)->expiresAfter($ttl);
        $this->cacheInstance->save($item);
    }
}