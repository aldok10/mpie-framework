<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop;

use ArrayObject;
use Closure;
use Mpie\Aop\Collector\AspectCollector;
use Mpie\Aop\Contract\AspectInterface;
use Mpie\Di\Reflection;
use ReflectionException;

trait ProxyHandler
{
    /**
     * @throws ReflectionException
     */
    protected static function __callViaProxy(string $method, Closure $callback, array $parameters): mixed
    {
        $class = static::class;
        /** @var AspectInterface $aspect */
        $pipeline = array_reduce(
            array_reverse(AspectCollector::getMethodAspects($class, $method)),
            fn ($stack, $aspect) => fn (JoinPoint $joinPoint) => $aspect->process($joinPoint, $stack),
            fn (JoinPoint $joinPoint) => $joinPoint->process()
        );
        $funcArgs         = new ArrayObject();
        $methodParameters = Reflection::methodParameterNames($class, $method);
        foreach ($parameters as $key => $parameter) {
            $funcArgs->offsetSet($methodParameters[$key], $parameter);
        }
        return $pipeline(new JoinPoint($class, $method, $funcArgs, $callback));
    }
}
