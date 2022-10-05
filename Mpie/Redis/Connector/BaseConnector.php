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
use Redis;

class BaseConnector implements ConnectorInterface
{
    public function __construct(
        protected string $host = '127.0.0.1',
        protected int $port = 6379,
        protected float $timeout = 0.0,
        protected $reserved = null,
        protected int $retryInterval = 0,
        protected float $readTimeout = 0.0,
        protected string $auth = '',
        protected int $database = 0,
    ) {
    }

    public function get()
    {
        $redis = new Redis();
        $redis->connect(
            $this->host,
            $this->port,
            $this->timeout,
            $this->reserved,
            $this->retryInterval,
            $this->readTimeout
        );
        $redis->select($this->database);
        $this->auth && $redis->auth($this->auth);
        return new RedisProxy($this, $redis);
    }

    public function release($connection)
    {
    }
}
