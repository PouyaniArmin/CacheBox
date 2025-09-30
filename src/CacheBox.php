<?php

namespace Armin\CacheBox;

use Exception;

/**
 * CacheBox is the main entry point for working with different cache drivers.
 * It allows switching between multiple caching backends (File, Memcached, Redis)
 * and provides a unified interface for cache operations.
 */
class CacheBox
{
    /** @var object|null The active cache driver instance */
    private $cacheDriver;

    /** @var array<string,string> List of supported cache drivers */
    private array $drivers = [
        'file' => FileCache::class,
        'memcached' => MemcacheDriver::class,
        'redis' => RedisDriver::class
    ];

    /**
     * Select the cache driver type.
     *
     * @param string $type The driver name (file, memcached, redis)
     * @return self
     * @throws Exception If the driver is not supported
     */
    public function driver(string $type)
    {
        if (!isset($this->drivers[$type])) {
            throw new Exception("Driver $type Not Supported");
        }
        $clasName = $this->drivers[$type];
        $this->cacheDriver = new $clasName;
        return $this;
    }

    /**
     * Set the cache storage path (used for FileCache).
     *
     * @param string $path
     * @return self
     */
    public function path(string $path)
    {
        $this->cacheDriver->path($path);
        return $this;
    }

    /**
     * Set the server connection details (Memcached/Redis).
     *
     * @param string $host
     * @param int $port
     * @return self
     */
    public function server(string $host, int $port)
    {
        $this->cacheDriver->setServer($host, $port);
        return $this;
    }

    /**
     * Set the cache directory (used for FileCache).
     *
     * @param string $directory
     * @return self
     */
    public function directory(string $directory)
    {
        $this->cacheDriver->directory($directory);
        return $this;
    }

    /**
     * Set the storage format (json, serialize, txt).
     *
     * @param string $type
     * @return self
     */
    public function format(string $type)
    {
        $this->cacheDriver->setFormat($type);
        return $this;
    }

    /**
     * Store a value in the cache.
     *
     * @param string $key Cache key
     * @param mixed $value Data to store
     * @param string|null $ttl Expiration time (e.g., "10s", "5m", "1h")
     */
    public function set(string $key, mixed $value, ?string $ttl = null)
    {
        $this->cacheDriver->set($key, $value, $ttl);
    }

    /**
     * Retrieve a value from the cache.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->cacheDriver->get($key);
    }

    /**
     * Delete a specific cache entry.
     *
     * @param string $key
     */
    public function delete(string $key)
    {
        $this->cacheDriver->delete($key);
    }

    /**
     * Clear all cache entries.
     */
    public function clear()
    {
        $this->cacheDriver->clear();
    }
}
