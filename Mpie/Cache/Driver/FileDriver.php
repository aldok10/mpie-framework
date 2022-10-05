<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Cache\Driver;

use Mpie\Cache\Exception\CacheException;
use Throwable;

class FileDriver extends AbstractDriver
{
    protected string $path;

    /**
     * @throws CacheException
     */
    public function __construct(array $config)
    {
        if (file_exists($path = $config['path'])) {
            if (is_file($path)) {
                throw new CacheException('已经存在同名文件，不能创建文件夹!');
            }
            if (! is_writable($path) || ! is_readable($path)) {
                chmod($path, 0755);
            }
        } else {
            mkdir($path, 0755, true);
        }
        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . '/';
    }

    public function has(string $key): bool
    {
        try {
            $cacheFile = $this->getFile($key);
            if (file_exists($cacheFile)) {
                $expire = (int) unserialize($this->getCache($cacheFile))[0];
                if ($expire !== 0 && filemtime($cacheFile) + $expire < time()) {
                    $this->remove($key);
                    return false;
                }
                return true;
            }
            return false;
        } catch (Throwable) {
            return false;
        }
    }

    public function get(string $key): mixed
    {
        if ($this->has($key)) {
            return unserialize($this->getCache($this->getFile($key)))[1];
        }
        return null;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return (bool) file_put_contents($this->getFile($key), serialize([(int) $ttl, $value]));
    }

    public function clear(): bool
    {
        try {
            $this->unlink($this->path);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            return $this->remove($key);
        }
        return true;
    }

    /**
     * TODO uses yield optimization.
     */
    protected function unlink(string $dir)
    {
        foreach (glob(rtrim($dir, '/') . '/*') as $item) {
            if (is_dir($item)) {
                $this->unlink($item);
                rmdir($item);
            } else {
                unlink($item);
            }
        }
    }

    /**
     * Get cached content.
     */
    protected function getCache(string $cacheFile): bool|string
    {
        return file_get_contents($cacheFile);
    }

    /**
     * cache hash.
     */
    protected function getID(string $key): string
    {
        return md5(strtolower($key));
    }

    /**
     * To delete a cache, it must be called when the cache
     * is known to exist, otherwise an error will be reported.
     */
    protected function remove(string $key): bool
    {
        return unlink($this->getFile($key));
    }

    /**
     * Get the file by key.
     */
    protected function getFile(string $key): string
    {
        return $this->path . $this->getID($key);
    }
}
