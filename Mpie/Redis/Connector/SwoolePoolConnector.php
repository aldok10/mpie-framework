<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Redis\Connector;

use Mpie\Redis\Contract\ConnectorInterface;
use Mpie\Redis\RedisProxy;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

class SwoolePoolConnector implements ConnectorInterface
{
    protected RedisPool $pool;

    public function __construct(
        protected string $host = '127.0.0.1',
        protected int $port = 6379,
        protected float $timeout = 0.0,
        protected string $reserved = '',
        protected int $retryInterval = 0,
        protected float $readTimeout = 0.0,
        protected string $auth = '',
        protected int $database = 0,
        protected int $poolSize = 32,
    ) {
        $redisConfig = (new RedisConfig())
            ->withHost($this->host)
            ->withPort($this->port)
            ->withTimeout($this->timeout)
            ->withReadTimeout($this->readTimeout)
            ->withRetryInterval($this->retryInterval)
            ->withReserved($this->reserved)
            ->withDbIndex($this->database)
            ->withAuth($this->auth);
        $this->pool  = new RedisPool($redisConfig, $this->poolSize);
    }

    public function get()
    {
        return new RedisProxy($this, $this->pool->get());
    }

    public function release($connection)
    {
        $this->pool->put($connection);
    }
}
