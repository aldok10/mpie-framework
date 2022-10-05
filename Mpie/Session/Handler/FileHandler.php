<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Session\Handler;

use Closure;
use Exception;
use FilesystemIterator;
use Generator;
use Mpie\Utils\Traits\AutoFillProperties;
use SessionHandlerInterface;
use SplFileInfo;
use Throwable;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function random_int;
use function rtrim;
use function time;
use function unlink;

class FileHandler implements SessionHandlerInterface
{
    use AutoFillProperties;

    protected string $path = '/tmp';

    protected int $gcDivisor = 100;

    protected int $gcProbability = 1;

    protected int $gcMaxLifetime = 1440;

    public function __construct(array $options = [])
    {
        $this->fillProperties($options);
        ! is_dir($this->path) && mkdir($this->path, 0755, true);
    }

    /**
     * @param int $mpieLifeTime
     */
    #[\ReturnTypeWillChange]
    public function gc($mpieLifeTime): int|false
    {
        try {
            $number = 0;
            $now    = time();
            $files  = $this->findFiles($this->path, function (SplFileInfo $item) use ($mpieLifeTime, $now) {
                return $now - $mpieLifeTime > $item->getMTime();
            });

            foreach ($files as $file) {
                $this->unlink($file->getPathname());
                ++$number;
            }
            return $number;
        } catch (Throwable) {
            return false;
        }
    }

    public function delete(string $id): bool
    {
        try {
            return $this->unlink($this->getSessionFile($id));
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param string $id
     */
    #[\ReturnTypeWillChange]
    public function read($id): string|false
    {
        $sessionFile = $this->getSessionFile($id);
        if (file_exists($sessionFile)) {
            return file_get_contents($sessionFile) ?: '';
        }

        return '';
    }

    /**
     * @param string $id
     * @param string $data
     */
    #[\ReturnTypeWillChange]
    public function write($id, $data): bool
    {
        return (bool) file_put_contents($this->getSessionFile($id), $data, LOCK_EX);
    }

    /**
     * @throws Exception
     */
    #[\ReturnTypeWillChange]
    public function close(): bool
    {
        // Garbage collection
        if (random_int(1, $this->gcDivisor) <= $this->gcProbability) {
            $this->gc($this->gcMaxLifetime);
        }
        return true;
    }

    /**
     * @param string $id
     */
    #[\ReturnTypeWillChange]
    public function destroy($id): bool
    {
        return $this->unlink($this->getSessionFile($id));
    }

    /**
     * Open session.
     */
    #[\ReturnTypeWillChange]
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Find files.
     */
    protected function findFiles(string $root, Closure $filter): Generator
    {
        $items = new FilesystemIterator($root);

        /** @var SplFileInfo $item */
        foreach ($items as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                yield from $this->findFiles($item->getPathname(), $filter);
            } elseif ($filter($item)) {
                yield $item;
            }
        }
    }

    /**
     * Generate session filename.
     */
    protected function getSessionFile(string $id): string
    {
        return rtrim($this->path, '/\\') . '/sess_' . $id;
    }

    /**
     * After judging whether the file exists, delete it.
     */
    private function unlink(string $file): bool
    {
        return is_file($file) && unlink($file);
    }
}
