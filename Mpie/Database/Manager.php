<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Database;

use Closure;
use Exception;
use Mpie\Database\Contract\ConnectorInterface;
use Mpie\Database\Eloquent\Model;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class Manager
{
    protected string $default = 'mysql';

    protected array $connectors = [];

    protected array $config = [];

    protected static ?EventDispatcherInterface $eventDispatcher = null;

    public function setDefault(string $name): void
    {
        $this->default = $name;
    }

    public function addConnector(string $name, ConnectorInterface $connector): void
    {
        $this->connectors[$name] = $connector;
    }

    public function query(string $name = ''): Query
    {
        $name = $name ?: $this->default;
        if (! isset($this->connectors[$name])) {
            throw new RuntimeException('No associated database connection');
        }

        return new Query($this->connectors[$name], static::$eventDispatcher);
    }

    /**
     * @throws Exception
     */
    public function extend(string $name, Closure $resolver): void
    {
        $connector = ($resolver)($this);
        if (! $connector instanceof ConnectorInterface) {
            throw new Exception('The resolver should return an instance of ConnectorInterface');
        }
        $this->addConnector($name, $connector);
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        static::$eventDispatcher = $eventDispatcher;
    }

    public function bootEloquent(): void
    {
        Model::setManager($this);
    }
}
