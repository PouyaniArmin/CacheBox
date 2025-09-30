<?php

namespace Armin\CacheBox;

use Exception;

/**
 * FileCache driver for storing cache entries in local filesystem.
 * Supports multiple formats (json, serialize, txt) and TTL expiration.
 */
class FileCache extends CacheDriverAbstract
{
    /** @var string|null Base path for cache files */
    private ?string $pathCache = null;

    /** @var string|null Subdirectory for cache storage */
    private ?string $directoryCache = 'cacheBox';

    /** @var string|null Current format (json, serialize, txt) */
    private ?string $format;

    /** @var array List of supported formats */
    private array $formats = [
        'json' => 'json',
        'serialize' => 'serialize',
        'txt' => 'txt'
    ];

    /**
     * Set the base path for cache files.
     *
     * @param string $path
     */
    public function path(string $path)
    {
        $this->pathCache = $path;
    }

    /**
     * Set the storage format for cache files.
     *
     * @param string $type
     * @return string
     * @throws Exception If format is unsupported
     */
    public function setFormat(string $type)
    {
        if (!isset($this->formats[$type])) {
            throw new Exception("Error Format NotFound");
        }
        $this->format = $this->formats[$type];
        return $this->format;
    }

    /**
     * Set the subdirectory for storing cache files.
     *
     * @param string $directory
     */
    public function directory(string $directory)
    {
        $this->directoryCache = $directory;
    }

    /**
     * Get the base path.
     *
     * @return string
     * @throws Exception If path is not initialized
     */
    protected function getPath(): string
    {
        if (!$this->pathCache) {
            throw new Exception("Error: This is an uninitialized Path. Call path() method first.");
        }
        return $this->pathCache;
    }

    /**
     * Get the cache subdirectory.
     *
     * @return string
     * @throws Exception If directory is not initialized
     */
    protected function getDirectory(): string
    {
        if (!$this->directoryCache) {
            throw new Exception("Error: This is an uninitialized directory. Call directory() method first.");
        }
        return $this->directoryCache;
    }

    /**
     * Create the cache directory if it does not exist.
     *
     * @return string Full path to the cache directory
     * @throws Exception If directory creation fails
     */
    protected function createCacheDirectory(): string
    {
        $file_path = $this->getPath() . "/" . $this->getDirectory();
        if (!is_dir($file_path) && !mkdir($file_path, 0755, true)) {
            throw new Exception("Unable to create cache directory: $file_path");
        }
        return $file_path;
    }

    /**
     * Store a value in the cache with optional TTL.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $ttl
     */
    public function set(string $key, mixed $value, ?string $ttl = null)
    {
        $filePath = $this->createCacheDirectory() . '/' . $key . '.' . $this->format;
        $dataToStore = [
            'created_at' => time(),
            'expires_at' => time() + $this->convertTtlToSeconds($ttl),
            'value' => $value
        ];

        if ($this->format === 'txt' || $this->format === 'serialize') {
            file_put_contents($filePath, serialize($dataToStore));
        } elseif ($this->format === 'json') {
            $valueDecoded = is_string($value) ? json_decode($value, true) : $value;
            $dataToStore['value'] = $valueDecoded;
            file_put_contents($filePath, json_encode($dataToStore, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    /**
     * Retrieve a value from the cache.
     *
     * @param string $key
     * @return mixed|null
     * @throws Exception If file does not exist
     */
    public function get(string $key): mixed
    {
        $file = $this->createCacheDirectory() . '/' . $key . '.' . $this->format;

        if (!file_exists($file)) {
            throw new Exception("Error: File does not exist at path: $file");
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

    /**
     * Delete a cache entry.
     *
     * @param string $key
     * @return void
     * @throws Exception If file does not exist
     */
    public function delete(string $key)
    {
        $file = $this->createCacheDirectory() . '/' . $key . '.' . $this->format;
        if (!file_exists($file)) {
            throw new Exception("Error: File does not exist at path: $file");
        }
        unlink($file);
    }

    /**
     * Clear all cache files in the directory.
     *
     * @return int Number of deleted files
     */
    public function clear()
    {
        $dir = $this->createCacheDirectory();
        $scan = scandir($dir);
        $deletedCount = 0;
        foreach ($scan as $item) {
            if ($item === '.' || $item === '..') continue;
            if (is_file($dir . "/$item")) {
                unlink($dir . "/$item");
                $deletedCount++;
            }
        }
        return $deletedCount;
    }
}
