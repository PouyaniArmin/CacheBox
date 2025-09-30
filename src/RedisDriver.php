<?php

namespace Armin\CacheBox;

use Exception;
use Redis;

/**
 * RedisDriver handles caching using Redis server.
 * No explicit format required as Redis can store multiple types natively.
 */
class RedisDriver extends CacheDriverAbstract
{
    /** @var Redis Instance of Redis client */
    private Redis $redis;

    /**
     * Connect to a Redis server and check if it's alive.
     *
     * @param string $host
     * @param int $port
     * @throws Exception If Redis server is not reachable
     */
    public function setServer(string $host, int $port)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);

        if (!$this->redis->ping()) {
            throw new Exception("Error: Could not connect to Redis. Please check server $host:$port");
        }
    }

    /**
     * Store a value in Redis with optional TTL.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, ?string $ttl = null)
    {
        $expires = $this->convertTtlToSeconds($ttl);
        $this->redis->set($key, $value, $expires);
        return true;
    }

    /**
     * Retrieve a value from Redis.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key): mixed
    {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return $data;
    }

    /**
     * Delete a key from Redis.
     *
     * @param string $key
     */
    public function delete(string $key)
    {
        $this->redis->del($key);
    }

    /**
     * Clear all keys from the current Redis database.
     */
    public function clear()
    {
        $this->redis->flushDB();
    }
}
