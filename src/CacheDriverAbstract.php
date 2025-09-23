<?php
namespace Armin\CacheBox;

use Exception;

abstract class CacheDriverAbstract{
   abstract public function set(string $key,mixed $value,?string $ttl=null);
   abstract public function get(string $key):mixed;
   abstract public function delete(string $key);
   abstract public function clear();
   protected function convertTtlToSeconds(?string $ttl):?int{
      if ($ttl ===null) {
         return null;
      }
      $unit=strtolower(substr($ttl,-1));
      $value=(int)substr($ttl,0,-1);
      return match ($unit) {
          's'=>$value ,
          'm'=>$value*60 ,
          'h'=>$value*3600,
          'd'=>$value*86400,
          default=>throw new Exception("Invalid TTL format: $ttl")
      };
   }
}