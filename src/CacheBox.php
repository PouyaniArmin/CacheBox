<?php

namespace Armin\CacheBox;

use Exception;

class CacheBox
{
    private $cacheDriver;
    private array $drivers = [
        'file' => FileCache::class,
        'memcached' => MemcacheDriver::class,
        'redis' => RedisDriver::class
    ];
    public function driver(string $type)
    {
        if (!isset($this->drivers[$type])) {
            throw new Exception("Dirver $type Not Supported");
        }
        $clasName = $this->drivers[$type];
        $this->cacheDriver = new $clasName;
        return $this;
    }
    public function path(string $path)
    {
        $this->cacheDriver->path($path);
        return $this;
    }
    public function server(string $host, int $port)
    {
        $this->cacheDriver->setServer($host, $port);
        return $this;
    }
    public function directory(string $directory)
    {
        $this->cacheDriver->directory($directory);
        return $this;
    }
    public function format(string $type)
    {
        $this->cacheDriver->setFormat($type);
        return $this;
    }
    public function set(string $key, mixed $value, ?string $ttl = null)
    {
        $this->cacheDriver->set($key, $value, $ttl);
    }
    public function get(string $key): mixed
    {
        return $this->cacheDriver->get($key);
    }
    public function delete(string $key)
    {
        $this->cacheDriver->delete($key);
    }
    public function clear()
    {
        $this->cacheDriver->clear();
    }
}
