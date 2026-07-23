<?php
namespace App;

/**
 * Simple Cache Wrapper (Redis-like interface with file fallback)
 * In production, this would be replaced with actual Redis/Memcached
 */
class Cache {
    private $prefix = 'olms_';
    private $default_ttl = 3600; // 1 hour
    private $use_redis = false;
    private $redis = null;

    public function __construct() {
        // Try to connect to Redis if available
        if (class_exists('Redis')) {
            try {
                $this->redis = new Redis();
                $this->redis->connect(getenv('REDIS_HOST') ?: '127.0.0.1',
                                    getenv('REDIS_PORT') ?: 6379);
                // Test connection
                $this->redis->ping();
                $this->use_redis = true;
            } catch (\Exception $e) {
                // Fall back to file-based cache
                $this->use_redis = false;
            }
        }

        // Ensure cache directory exists for file fallback
        if (!$this->use_redis) {
            $cacheDir = __DIR__ . '/../../cache';
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
        }
    }

    /**
     * Get item from cache
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public function get($key, $default = null) {
        $key = $this->prefix . $key;

        if ($this->use_redis) {
            $value = $this->redis->get($key);
            if ($value === false) {
                return $default;
            }
            return unserialize($value);
        } else {
            $file = $this->getFilePath($key);
            if (!is_file($file)) {
                return $default;
            }

            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                // Expired, delete it
                unlink($file);
                return $default;
            }

            return $data['value'];
        }
    }

    /**
     * Set item in cache
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return bool Success
     */
    public function set($key, $value, $ttl = null) {
        $key = $this->prefix . $key;
        $ttl = $ttl ?? $this->default_ttl;
        $expires = time() + $ttl;

        if ($this->use_redis) {
            return $this->redis->setex($key, $ttl, serialize($value));
        } else {
            $file = $this->getFilePath($key);
            $data = [
                'value' => $value,
                'expires' => $expires
            ];
            return file_put_contents($file, serialize($data)) !== false;
        }
    }

    /**
     * Delete item from cache
     * @param string $key Cache key
     * @return bool Success
     */
    public function delete($key) {
        $key = $this->prefix . $key;

        if ($this->use_redis) {
            return $this->redis->del($key);
        } else {
            $file = $this->getFilePath($key);
            if (is_file($file)) {
                return unlink($file);
            }
            return true;
        }
    }

    /**
     * Clear all cache items with our prefix
     * @return bool Success
     */
    public function clear() {
        if ($this->use_redis) {
            $keys = $this->redis->keys($this->prefix . '*');
            if (!empty($keys)) {
                return $this->redis->del($keys);
            }
            return true;
        } else {
            $cacheDir = __DIR__ . '/../../cache';
            $files = glob($cacheDir . '/' . $this->prefix . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            return true;
        }
    }

    /**
     * Get file path for cache key (file fallback)
     * @param string $key Cache key
     * @return string File path
     */
    private function getFilePath($key) {
        $cacheDir = __DIR__ . '/../../cache';
        return $cacheDir . '/' . md5($key) . '.cache';
    }
}