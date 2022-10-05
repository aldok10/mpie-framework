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
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ContextTest extends TestCase
{
    public function testHasContainer()
    {
        $this->assertTrue(Context::hasContainer());
    }

    public function testGetContainer()
    {
        $this->assertTrue(Context::getContainer() instanceof ContainerInterface);
    }

    public function testSetContainer()
    {
        Context::setContainer(new Container());
        $this->assertTrue(Context::getContainer() instanceof ContainerInterface);
    }
}
