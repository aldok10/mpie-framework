<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Redis;

use Closure;
use Mpie\Redis\Contract\ConnectorInterface;

/**
 * @mixin \Redis
 */
class Redis
{
    public function __construct(
        protected ConnectorInterface $connector
    ) {
    }

    public function __call(string $name, array $arguments)
    {
        return $this->getHandler()->{$name}(...$arguments);
    }

    public function getHandler()
    {
        return $this->connector->get();
    }

    public function wrap(Closure $callable)
    {
        return $callable($this->getHandler());
    }
}
