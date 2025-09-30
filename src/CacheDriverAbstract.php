<?php
namespace Armin\CacheBox;

use Exception;

/**
 * Abstract base class for all cache drivers.
 * Defines the required interface and provides common utilities like TTL conversion.
 */
abstract class CacheDriverAbstract {

    /**
     * Store a value in the cache.
     *
     * @param string $key   The cache key.
     * @param mixed $value  The value to store.
     * @param string|null $ttl Time-to-live (e.g., "10s", "5m", "2h", "1d").
     * @return mixed
     */
    abstract public function set(string $key, mixed $value, ?string $ttl = null);

    /**
     * Retrieve a value from the cache.
     *
     * @param string $key The cache key.
     * @return mixed The cached value or null if not found/expired.
     */
    abstract public function get(string $key): mixed;

    /**
     * Delete a value from the cache.
     *
     * @param string $key The cache key.
     * @return mixed
     */
    abstract public function delete(string $key);

    /**
     * Clear all cache entries.
     *
     * @return mixed
     */
    abstract public function clear();

    /**
     * Convert TTL string into seconds.
     *
     * @param string|null $ttl TTL string (e.g., "10s", "5m", "2h", "1d").
     * @return int|null TTL in seconds or null if no TTL provided.
     * @throws Exception If format is invalid.
     */
    protected function convertTtlToSeconds(?string $ttl): ?int {
        if ($ttl === null) {
            return null;
        }
        $unit = strtolower(substr($ttl, -1));
        $value = (int)substr($ttl, 0, -1);

        return match ($unit) {
            's' => $value,
            'm' => $value * 60,
            'h' => $value * 3600,
            'd' => $value * 86400,
            default => throw new Exception("Invalid TTL format: $ttl")
        };
    }
}
