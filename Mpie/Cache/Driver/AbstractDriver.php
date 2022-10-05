<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Cache\Driver;

use Mpie\Cache\Contract\CacheDriverInterface;

abstract class AbstractDriver implements CacheDriverInterface
{
    public function increment(string $key, int $step = 1): int|bool
    {
        $value = (int) $this->get($key) + $step;
        $this->set($key, $value);
        return $value;
    }

    public function decrement(string $key, int $step = 1): int|bool
    {
        return $this->increment($key, -$step);
    }
}
