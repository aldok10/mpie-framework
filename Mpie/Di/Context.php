<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Di;

use Psr\Container\ContainerInterface;

class Context
{
    protected static ContainerInterface $container;

    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }

    public static function getContainer(): ContainerInterface|Container
    {
        if (! self::hasContainer()) {
            self::$container = new Container();
            self::$container->set(ContainerInterface::class, self::$container);
            self::$container->set(Container::class, self::$container);
        }
        return self::$container;
    }

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }
}
