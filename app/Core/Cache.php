<?php

namespace App\Core;

class Cache
{
    private const PREFIX = PREFIX_CACHE;
    private ?\Memcached $memcached = null;
    private bool $memcachedEnabled = false;

    public function __construct()
    {
        // Пытаемся подключить Memcached
        if (class_exists('Memcached') && extension_loaded('memcached')) {
            $this->memcached = new \Memcached();
            $this->memcached->addServer('localhost', 11211);

            // Проверяем подключение
            $stats = $this->memcached->getStats();
            $this->memcachedEnabled = !empty($stats);
        }
    }

    private function buildKey(string $key): string
    {
        return self::PREFIX . '_' . $key;
    }

    public function set(string $key, $data, int $seconds = 86400): bool
    {
        $key = $this->buildKey($key);

        if ($this->memcachedEnabled) {
            return $this->memcached->set($key, $data, $seconds);
        }

        // Файловый кэш
        $content = [
            'data' => $data,
            'expires' => time() + $seconds
        ];

        $cacheFile = CACHE_DIR . '/' . $key . '.cache';
        return file_put_contents($cacheFile, serialize($content)) !== false;
    }

    public function get(string $key, $default = null)
    {
        $key = $this->buildKey($key);

        if ($this->memcachedEnabled) {
            $data = $this->memcached->get($key);
            return $this->memcached->getResultCode() === \Memcached::RES_SUCCESS ? $data : $default;
        }

        // Файловый кэш
        $cacheFile = CACHE_DIR . '/' . $key . '.cache';

        if (!file_exists($cacheFile)) {
            return $default;
        }

        $content = unserialize(file_get_contents($cacheFile));

        if ($content['expires'] < time()) {
            unlink($cacheFile);
            return $default;
        }

        return $content['data'];
    }

    public function forget(string $key): bool
    {
        $key = $this->buildKey($key);

        if ($this->memcachedEnabled) {
            return $this->memcached->delete($key);
        }

        $cacheFile = CACHE_DIR . '/' . $key . '.cache';
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }

        return false;
    }

    public function flush(): bool
    {
        if ($this->memcachedEnabled) {
            return $this->memcached->flush();
        }

        // Удаляем только наши файлы кэша
        $files = glob(CACHE_DIR . '/' . self::PREFIX . '_*.cache');
        $success = true;

        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    public function remember(string $key, callable $callback, int $seconds = 86400)
    {
        $data = $this->get($key);

        if ($data !== null) {
            return $data;
        }

        $data = $callback();
        $this->set($key, $data, $seconds);

        return $data;
    }

    public function isMemcachedEnabled(): bool
    {
        return $this->memcachedEnabled;
    }
}