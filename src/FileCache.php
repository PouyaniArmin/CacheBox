<?php

namespace Armin\CacheBox;

use Exception;

class FileCache extends CacheDriverAbstract
{
    private ?string $pathCache = null;
    private ?string $directoryCache = 'cacheBox';
    private ?string $format;
    private array $formats = [
        'json' => 'json',
        'serialize' => 'serialize',
        'txt' => 'txt'
    ];
    public function path(string $path)
    {
        $this->pathCache = $path;
    }
    public function setFormat(string $type)
    {
        if (!isset($this->formats[$type])) {
            throw new Exception("Error Format NotFound");
        }
        $this->format = $this->formats[$type];
        return $this->format;
    }
    public function directory(string $directory)
    {
        $this->directoryCache = $directory;
    }
    protected function getPath(): string
    {
        if (!$this->pathCache) {
            throw new Exception("Error:This is a initilaize Path with init method");
        }
        return $this->pathCache;
    }
    protected function getDirectory(): string
    {
        if (!$this->directoryCache) {
            throw new Exception("Error:This is a initilaize directory with init method");
        }
        return $this->directoryCache;
    }
    protected function createCacheDirectory(): string
    {
        $file_path = $this->getPath() . "/" . $this->getDirectory();
        if (!is_dir($file_path) && !mkdir($file_path, 0755, true)) {
            throw new Exception("Unable to crate cache directory:$file_path");
        }
        return $file_path;
    }
    public function set(string $key, mixed $value, ?int $ttl = null)
    {
        $filePath = $this->createCacheDirectory() . '/' . $key . '.' . $this->format;
        if ($this->format === 'txt') {
            file_put_contents($filePath, $value);
        } elseif ($this->format === 'json') {
            $data = json_decode($value, true);
            $response = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            file_put_contents($filePath, $response);
        } elseif ($this->format === 'serialize') {
            $serialized = serialize($value);
            file_put_contents($filePath, $serialized);
        }
    }
    public function get(string $key) {}
    public function delete(string $key) {}
    public function clear() {}
}
