<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop\Collector;

use Mpie\Aop\Contract\CollectorInterface;

abstract class AbstractCollector implements CollectorInterface
{
    public static function collectClass(string $class, object $attribute): void
    {
    }

    public static function collectMethod(string $class, string $method, object $attribute): void
    {
    }

    public static function collectProperty(string $class, string $property, object $attribute): void
    {
    }

    public static function collectorMethodParameter(string $class, string $method, string $parameter, object $attribute)
    {
    }
}
