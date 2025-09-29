<?php


namespace Armin\CacheBox;

use Exception;
use Memcached;

class MemcacheDriver extends CacheDriverAbstract
{
    private Memcached $memcached;
    private array $formats = ['string' => 'string', 'json' => 'json'];
    private string $format;

    public function setServer(string $host, int $port)
    {
        $this->memcached = new Memcached();
        $this->memcached->addServer($host, $port);
        $status = $this->memcached->getVersion();
        if ($status === false) {
            throw new Exception("Connection to Memcached server at $host:$port failed.");
        }
    }

    public function setFormat(string $type)
    {
        if (!isset($this->formats[$type])) {
            throw new Exception("Error Format NotFound");
        }
        $this->format = $this->formats[$type];
        return $this->format;
    }

    public function set(string $key, mixed $value, ?string $ttl = null)
    {
        $ttlSeconds = $this->convertTtlToSeconds($ttl);
        if ($this->format === 'string') {
            $dataToStore = [
                'type' => $this->format,
                'value' => $value
            ];
            $storedValue = serialize($dataToStore);
            $this->memcached->set($key, $storedValue, $ttlSeconds);
        } elseif ($this->format === 'json') {
            $data = [
                'type' => $this->format,
                'value' => json_decode($value)
            ];
            $storedValue = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->memcached->set($key, $storedValue, $ttlSeconds);
        } else {
            throw new Exception("Unsupported format: {$this->format}");
        }
        return true;
    }
    public function get(string $key): mixed
    {
        $storedValue = $this->memcached->get($key);
        if ($storedValue === false) {
            return null;
        }
        $data = @unserialize($storedValue);
        if ($data !== false && is_array($data) && isset($data['type'])) {
            return $data['value'];
        }
        $data = json_decode($storedValue, true);
        if (json_last_error() === JSON_ERROR_NONE && $data !== false) {
            return $data['value'];
        }
        throw new Exception("Memcached error while fetching key: $key");
    }
    public function delete(string $key) {
        $result=$this->memcached->delete($key);       
        if ($result===false && $this->memcached->getResultCode()!==Memcached::RES_NOTFOUND) {
            throw new Exception("Failed to delete key: $key");
        }
        return $result;
    }
    public function clear() {
        return $this->memcached->flush();
    }
}
