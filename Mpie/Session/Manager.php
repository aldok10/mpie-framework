<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Session;

use Mpie\Config\Contract\ConfigInterface;
use SessionHandlerInterface;

class Manager
{
    protected SessionHandlerInterface $sessionHandler;

    public function __construct(ConfigInterface $config)
    {
        $config               = $config->get('session');
        $handler              = $config['handler'];
        $config               = $config['config'];
        $this->sessionHandler = new $handler($config);
    }

    /**
     * Establish a new session.
     */
    public function create(): Session
    {
        return new Session($this->sessionHandler);
    }
}
