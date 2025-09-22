<?php

namespace Armin\CacheBox;

use Exception;

class CacheBox
{
    private $cacheDriver;
    private array $drivers = ['file' => FileCache::class];
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
    public function set(string $key, mixed $value, ?int $ttl = null)
    {
        $this->cacheDriver->set($key, $value, $ttl);
    }
    public function get(string $key)
    {
        $this->cacheDriver->get($key);
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
