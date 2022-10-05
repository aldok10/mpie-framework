<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Redis;

use Mpie\Redis\Contract\ConnectorInterface;
use RedisException;

/**
 * @mixin \Redis
 */
class RedisProxy
{
    public function __construct(
        protected ConnectorInterface $connector,
        protected $redis
    ) {
    }

    public function __destruct()
    {
        $this->connector->release($this->redis);
    }

    /**
     * @throws RedisException
     */
    public function __call(string $name, array $arguments)
    {
        try {
            return $this->redis->{$name}(...$arguments);
        } catch (RedisException $e) {
            $this->redis = null;
            throw $e;
        }
    }

    public function getRedis()
    {
        return $this->redis;
    }
}
