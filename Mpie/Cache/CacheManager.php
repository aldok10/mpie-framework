<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Cache;

use InvalidArgumentException;
use Mpie\Config\Contract\ConfigInterface;
use Psr\SimpleCache\CacheInterface;

class CacheManager
{
    /**
     * @var array|mixed
     */
    protected array $config;

    /**
     * @var mixed|string
     */
    protected string $defaultStore;

    protected array $stores = [];

    public function __construct(ConfigInterface $config)
    {
        $config             = $config->get('cache');
        $this->defaultStore = $config['default'];
        $this->config       = $config['stores'];
    }

    public function store(?string $name = null): CacheInterface
    {
        $name ??= $this->defaultStore;
        if (! isset($this->stores[$name])) {
            if (! isset($this->config[$name])) {
                throw new InvalidArgumentException('配置不正确');
            }
            $config              = $this->config[$name];
            $driver              = $config['driver'];
            $this->stores[$name] = new Cache(new ($driver)($config['config']));
        }
        return $this->stores[$name];
    }
}
