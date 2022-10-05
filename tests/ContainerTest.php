<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Tests;

use Mpie\Di\Container;
use Mpie\Di\Context;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ContainerTest extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = Context::getContainer();
    }

    public function testBind()
    {
        $this->container->bind(FooInterface::class, Foo::class);
        $this->assertEquals($this->container->getBinding(FooInterface::class), Foo::class);
    }
}
