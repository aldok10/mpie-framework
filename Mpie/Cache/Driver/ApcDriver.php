<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Cache\Driver;

class ApcDriver extends AbstractDriver
{
    public function has(string $key): bool
    {
        return (bool) \apc_exists($key);
    }

    public function get(string $key): mixed
    {
        $data = \apc_fetch($key, $success);
        return $success === true ? $data : null;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return (bool) \apc_store($key, $value, (int) $ttl);
    }

    public function clear(): bool
    {
        return \apc_clear_cache('user');
    }

    public function delete(string $key): bool
    {
        return (bool) \apc_delete($key);
    }

    public function increment(string $key, int $step = 1): int|bool
    {
        return \apc_inc($key, $step);
    }

    public function decrement(string $key, int $step = 1): int|bool
    {
        return \apc_dec($key, $step);
    }
}
