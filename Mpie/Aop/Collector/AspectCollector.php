<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop\Collector;

use Mpie\Aop\Annotation\AspectConfig;
use Mpie\Aop\Contract\AspectInterface;
use Mpie\Aop\Scanner;
use Mpie\Di\Reflection;
use ReflectionException;

class AspectCollector extends AbstractCollector
{
    protected static array $container = [];

    /**
     * Collection method section.
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if ($attribute instanceof AspectInterface) {
            self::$container[$class][$method][] = $attribute;
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof AspectInterface) {
            foreach (Reflection::class($class)->getMethods() as $reflectionMethod) {
                if (! $reflectionMethod->isConstructor()) {
                    self::$container[$class][$reflectionMethod->getName()][] = $attribute;
                }
            }
        } elseif ($attribute instanceof AspectConfig) {
            $reflectionClass = Reflection::class($attribute->class);
            $annotation      = new $class(...$attribute->params);
            $methods         = $attribute->methods;
            if ($methods === '*') {
                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    if (! $reflectionMethod->isConstructor()) {
                        self::$container[$attribute->class][$reflectionMethod->getName()][] = $annotation;
                    }
                }
            } else {
                foreach ((array) $methods as $method) {
                    self::$container[$attribute->class][$method][] = $annotation;
                }
            }
            Scanner::instance()->addClass($attribute->class, $reflectionClass->getFileName());
        }
    }

    /**
     * Returns an aspect of a class method.
     */
    public static function getMethodAspects(string $class, string $method): array
    {
        return self::$container[$class][$method] ?? [];
    }

    /**
     * Returns the collected class.
     *
     * @return string[]
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }
}
