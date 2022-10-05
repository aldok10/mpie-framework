<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

use Mpie\Di\Context;
use Mpie\Di\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

if (function_exists('container') === false) {
    /**
     * Container instantiation and getting instances.
     */
    function container(): ContainerInterface
    {
        return Context::getContainer();
    }
}

if (function_exists('call') === false) {
    /**
     * The container calls the method.
     *
     * @param array|Closure|string $callback  Arrays, closures, function names
     * @param array                $arguments list of parameters passed to the method
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    function call(array|string|Closure $callback, array $arguments = []): mixed
    {
        return container()->call($callback, $arguments);
    }
}

if (function_exists('make') === false) {
    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
     * @throws NotFoundException
     * @throws ContainerExceptionInterface|ReflectionException
     */
    function make(string $id, array $parameters = [])
    {
        return container()->make($id, $parameters);
    }
}
