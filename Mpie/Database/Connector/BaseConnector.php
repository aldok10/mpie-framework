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
use PDO;

class BaseConnector implements ConnectorInterface
{
    public function __construct(
        protected DBConfig $config
    ) {
    }

    /**
     * @return PDO
     */
    public function get()
    {
        return new PDO(
            $this->config->getDsn(),
            $this->config->getUser(),
            $this->config->getPassword(),
            $this->config->getOptions()
        );
    }

    public function release($connection)
    {
    }
}
