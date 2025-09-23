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
    public function set(string $key, mixed $value, ?string $ttl = null)
    {
        $filePath = $this->createCacheDirectory() . '/' . $key . '.' . $this->format;
        if ($this->format === 'txt') {
            $dataToStore = [
                'created_at' => time(),
                'expires_at' => time() + $this->convertTtlToSeconds($ttl),
                'value' => $value
            ];
            file_put_contents($filePath, serialize($dataToStore));
        } elseif ($this->format === 'json') {
            $valueDecoded = is_string($value) ? json_decode($value, true) : $value;
            $dataToStore = [
                'created_at' => time(),
                'expires_at' => time() + $this->convertTtlToSeconds($ttl),
                'value' => $valueDecoded
            ];
            file_put_contents($filePath, json_encode($dataToStore, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } elseif ($this->format === 'serialize') {

            $dataToStore = [
                'created_at' => time(),
                'expires_at' => time() + $this->convertTtlToSeconds($ttl),
                'value' => $value
            ];
            file_put_contents($filePath, serialize($dataToStore));
        }
    }
    public function get(string $key): mixed
    {
        $file = $this->createCacheDirectory() . '/' . $key . '.' . $this->format;

        if (!file_exists($file)) {
            throw new Exception("Error File Not Exists Path: $file");
        }

        $response = file_get_contents($file);

        if ($this->format === 'txt' || $this->format === 'serialize') {
            $data = unserialize($response);
        } elseif ($this->format === 'json') {
            $data = json_decode($response, true);
        }

        $expires_at = $data['expires_at'] ?? null;
        if ($expires_at && time() > $expires_at) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }


    public function delete(string $key)
    {
        $file = $this->createCacheDirectory() . '/' . $key . '.' . $this->format;
        if (!file_exists($file)) {
            throw new Exception("Error File Not Exists Path: $file");
        }
        unlink($file);
        return null;
    }
    public function clear()
    {
        $dir = $this->createCacheDirectory();
        $scan = scandir($dir);
        $deletedCount = 0;
        foreach ($scan as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (is_file($dir . "/$item")) {
                unlink($dir . "/$item");
                $deletedCount++;
            }
        }
        return $deletedCount;
    }
}
