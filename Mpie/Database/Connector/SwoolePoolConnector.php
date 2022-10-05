<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Database\Connector;

use Mpie\Database\Contract\ConnectorInterface;
use Mpie\Database\DBConfig;
use Swoole\Coroutine;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

class SwoolePoolConnector implements ConnectorInterface
{
    protected PDOPool $PDOPool;

    public function __construct(
        protected DBConfig $config
    ) {
        $PDOConfig     = (new PDOConfig())
            ->withDriver($this->config->getDriver())
            ->withHost($this->config->getHost())
            ->withPort($this->config->getPort())
            ->withUnixSocket($this->config->getUnixSocket())
            ->withCharset($this->config->getCharset())
            ->withDbname($this->config->getDatabase())
            ->withUsername($this->config->getUser())
            ->withPassword($this->config->getPassword())
            ->withOptions($this->config->getOptions());
        $this->PDOPool = new PDOPool($PDOConfig, $this->config->getPoolSize());
    }

    public function get()
    {
        $connection = $this->PDOPool->get();
        Coroutine::defer(function () use ($connection) {
            $this->PDOPool->put($connection);
        });
        return $connection;
    }

    public function release($connection)
    {
        $this->PDOPool->put($connection);
    }
}
