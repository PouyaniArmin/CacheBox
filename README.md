# CacheBox

CacheBox is a flexible PHP caching library that allows developers to store and retrieve data easily using multiple drivers, including file system, Memcached, and Redis. It provides a unified API for caching and simplifies cache management in PHP projects.

## Installation

### Requirements

- PHP 8.0 or higher
- PHP extensions:
  - `redis` (for RedisDriver)
  - `memcached` (for MemcacheDriver)
- File write permissions (for FileCache)

### Installation via Composer

You can install CacheBox using Composer:

```bash
composer require armin-dev/cache-box
```
```php
require __DIR__ . '/vendor/autoload.php';

use ArminDev\CacheBox\CacheBox;

```
## Drivers

### Memcached
- Supports two formats: `string` and `json`.
- Data must be stored in one of these formats.
- TTL (expiration) is fully supported.
- Requires Memcached server to be running and accessible.

### Redis
- No format selection required; Redis can store any type of data directly.
- TTL (expiration) is fully supported.
- Requires Redis server to be running and accessible.

## Usage / Examples

### Using FileCache

```php
use Armin\CacheBox\CacheBox;

$cache = new CacheBox();
$cache->driver('file')
      ->path(__DIR__ . '/cache')   // optional, default directory will be used
      ->directory('my_cache')      // optional
      ->format('json');            // set format: json, serialize, txt

$cache->set('post_1', ['title' => 'Hello World'], '1h');
$data = $cache->get('post_1');
print_r($data);
```

### Using Memcached
```php
use Armin\CacheBox\CacheBox;

$cache = new CacheBox();
$cache->driver('memcached')
      ->server('127.0.0.1', 11211)
      ->setFormat('json');        // required for Memcached

$cache->set('post_2', ['title' => 'Memcached Example'], '30m');
$data = $cache->get('post_2');
print_r($data);
```

### Using Redis
```php
use Armin\CacheBox\CacheBox;

$cache = new CacheBox();
$cache->driver('redis')
      ->server('127.0.0.1', 6379); // no format needed

$cache->set('post_3', ['title' => 'Redis Example'], '2h');
$data = $cache->get('post_3');
print_r($data);
```
## TTL / Expiration

CacheBox supports setting expiration times (TTL) for cached items. You can provide TTL as a string with a numeric value followed by a unit:

- `s` → seconds  
- `m` → minutes  
- `h` → hours  
- `d` → days  

**Examples:**

```php
use Armin\CacheBox\CacheBox;

$cache = new CacheBox();
$cache->driver('file')
      ->path(__DIR__ . '/cache')
      ->directory('my_cache')
      ->format('json');

// TTL in seconds
$cache->set('post_sec', 'Data expires in 30 seconds', '30s');

// TTL in minutes
$cache->set('post_min', 'Data expires in 15 minutes', '15m');

// TTL in hours
$cache->set('post_hour', 'Data expires in 2 hours', '2h');

// TTL in days
$cache->set('post_day', 'Data expires in 5 days', '5d');
```
## Configuration / Options

CacheBox provides some configurable options depending on the driver you use:

### FileCache
- `path($path)` → set the base path for cache files (required).  
- `directory($dir)` → set a subdirectory inside the base path (optional).  
- `format($type)` → choose the format: `json`, `serialize`, `txt` (required).

### Memcached
- `server($host, $port)` → set Memcached server host and port (required).  
- `setFormat($type)` → choose format: `string` or `json` (required).

### Redis
- `server($host, $port)` → set Redis server host and port (required).  
- No format selection is needed; Redis stores data directly.

**Example:**

```php
$cache = new CacheBox();
$cache->driver('file')
      ->path(__DIR__ . '/cache')
      ->directory('my_cache')
      ->format('json');

$cache->driver('memcached')
      ->server('127.0.0.1', 11211)
      ->setFormat('json');

$cache->driver('redis')
      ->server('127.0.0.1', 6379);
```
## Error Handling / Exceptions

CacheBox throws exceptions in cases where operations fail or invalid configurations are provided. You should handle these exceptions using `try/catch` blocks to prevent application crashes.

### Common Exceptions

CacheBox may throw exceptions in several scenarios. Examples:

```php
$cache = new CacheBox();

try {
    // Driver Not Supported
    $cache->driver('unknown');
} catch (Exception $e) {
    echo "Driver Error: " . $e->getMessage() . PHP_EOL;
}

try {
    // Redis server not reachable
    $cache->driver('redis')->server('127.0.0.1', 6379);
} catch (Exception $e) {
    echo "Redis Error: " . $e->getMessage() . PHP_EOL;
}

try {
    // Memcached server not reachable
    $cache->driver('memcached')->server('127.0.0.1', 11211);
} catch (Exception $e) {
    echo "Memcached Error: " . $e->getMessage() . PHP_EOL;
}

try {
    // FileCache file missing or expired
    $data = $cache->driver('file')->get('nonexistent_key');
} catch (Exception $e) {
    echo "FileCache Error: " . $e->getMessage() . PHP_EOL;
}
```
## Contributing

Contributions to CacheBox are welcome! You can help by:

- Reporting bugs or issues
- Suggesting new features
- Submitting pull requests with improvements

Before submitting a pull request, please make sure your code follows the existing coding standards and passes any tests.

---

## License

CacheBox is released under the MIT License. See the [LICENSE](LICENSE) file for more details.
