<?php

namespace Armin\CacheBox;

use Exception;
use Redis;

class RedisDriver extends CacheDriverAbstract
{
    private Redis $redis;
    public function setServer(string $host, int $port)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);
        if (!$this->redis->ping()) {
            throw new Exception("Error : Could not connect to Redis Please Check server $host:$port");
        }
    }
    public function set(string $key, mixed $value, ?string $ttl = null)
    {
        $expires = $this->convertTtlToSeconds($ttl);
        $this->redis->set($key, $value, $expires);
        return true;
    }
    public function get(string $key): mixed
    {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return $data;
    }
    public function delete(string $key)
    {
        $this->redis->del($key);
    }

    public function clear()
    {
        $this->redis->flushDB();
    }
}
