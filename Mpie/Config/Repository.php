<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Config;

use Mpie\Config\Contract\ConfigInterface;
use Mpie\Utils\Arr;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function pathinfo;

class Repository implements ConfigInterface
{
    /**
     * Configuration array.
     */
    protected array $items = [];

    /**
     * Get [support dot syntax].
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Set [support dot syntax]. Not available in environments such as Swoole/WorkerMan.
     */
    public function set(string $key, mixed $value): void
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Scan the directory.
     */
    public function scan(string|array $dirs): void
    {
        foreach ((array) $dirs as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if (! $file->isFile()) {
                    continue;
                }
                $path = $file->getRealPath() ?: $file->getPathname();
                if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
                    continue;
                }
                $this->loadOne($path);
                gc_mem_caches();
            }
        }
    }

    /**
     * All.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Load multiple configuration files.
     */
    public function load(string|array $files): void
    {
        is_array($files) ? $this->loadMany($files) : $this->loadOne($files);
    }

    /**
     * Load multiple configurations.
     */
    public function loadMany(array $files): void
    {
        foreach ($files as $file) {
            $this->loadOne($file);
        }
    }

    /**
     * Load a configuration file.
     */
    public function loadOne(string $file): void
    {
        $this->items[pathinfo($file, PATHINFO_FILENAME)] = include $file;
    }
}
