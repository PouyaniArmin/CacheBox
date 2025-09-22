<?php
namespace Armin\CacheBox;

abstract class CacheDriverAbstract{
   // abstract public function configuration(array $config);
   abstract public function set(string $key,mixed $value,?int $ttl=null);
   abstract public function get(string $key);
   abstract public function delete(string $key);
   abstract public function clear();
}