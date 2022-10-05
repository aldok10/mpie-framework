<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Di;

use BadMethodCallException;
use Closure;
use Mpie\Di\Exception\ContainerException;
use Mpie\Di\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionUnionType;

use function is_null;
use function is_object;
use function is_string;

class Container implements ContainerInterface
{
    /**
     * @var array Class and Identity Correspondence
     */
    protected array $bindings = [];

    /**
     * @var array parsed instance
     */
    protected array $resolvedEntries = [];

    /**
     * Store the instantiated class in an array.
     *
     * @param class-string|string $id       Mpie
     * @param object              $concrete Example
     */
    public function set(string $id, object $concrete)
    {
        $this->resolvedEntries[$this->getBinding($id)] = $concrete;
    }

    /**
     * Get Container.
     *
     * @return T
     * @throws NotFoundExceptionInterface
     */
    public function get(string $id)
    {
        $binding = $this->getBinding($id);
        if (isset($this->resolvedEntries[$binding])) {
            return $this->resolvedEntries[$binding];
        }

        throw new NotFoundException('No instance found: ' . $id);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return isset($this->resolvedEntries[$this->getBinding($id)]);
    }

    /**
     * @param string       $id    ID, which can be an interface
     * @param class-string $class class name
     */
    public function bind(string $id, string $class): void
    {
        $this->bindings[$id] = $class;
    }

    /**
     * @param string $id Mpie
     */
    public function unBind(string $id): void
    {
        if ($this->bound($id)) {
            unset($this->bindings[$id]);
        }
    }

    /**
     * @param string $id Mpie
     */
    public function bound(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * @param string $id Mpie
     */
    public function getBinding(string $id): string
    {
        return $this->bindings[$id] ?? $id;
    }

    /**
     * The injected external interface method.
     *
     * @template T
     *
     * @param class-string<T> $id        Mpie
     * @param array           $arguments Constructor parameter list (associative array)
     *
     * @return T
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ReflectionException
     */
    public function make(string $id, array $arguments = [])
    {
        if ($this->has($id) === false) {
            $id              = $this->getBinding($id);
            $reflectionClass = Reflection::class($id);
            if ($reflectionClass->isInterface()) {
                if (! $this->bound($id)) {
                    throw new ContainerException('The ' . $id . ' has no implementation class. ', 600);
                }
                // TODO when the bound class does not implement the interface
                $reflectionClass = Reflection::class($this->getBinding($id));
            }

            $this->set($id, $reflectionClass->newInstanceArgs($this->getConstructorArgs($reflectionClass, $arguments)));
        }
        return $this->get($id);
    }

    /**
     * Log out the instance.
     */
    public function remove(string $id): void
    {
        $binding = $this->getBinding($id);
        unset($this->resolvedEntries[$binding]);
        if ($id !== $binding && isset($this->resolvedEntries[$id])) {
            unset($this->resolvedEntries[$id]);
        }
    }

    /**
     * Call a method of the class.
     *
     * @param array|Closure|string $callable  $callable Callable class or array of instances and methods
     * @param array                $arguments Parameters passed to the method (associative array)
     *
     * @throws ContainerExceptionInterface|ReflectionException
     */
    public function call(array|string|Closure $callable, array $arguments = []): mixed
    {
        if ($callable instanceof Closure || is_string($callable)) {
            return $this->callFunc($callable, $arguments);
        }
        [$objectOrClass, $method] = $callable;
        $isObject                 = is_object($objectOrClass);
        $reflectionMethod         = Reflection::method($isObject ? get_class($objectOrClass) : $this->getBinding($objectOrClass), $method);
        if ($reflectionMethod->isAbstract() === false) {
            if (! $reflectionMethod->isPublic()) {
                $reflectionMethod->setAccessible(true);
            }

            return $reflectionMethod->invokeArgs(
                $reflectionMethod->isStatic() ? null : ($isObject ? $objectOrClass : $this->make($objectOrClass)),
                $this->getFuncArgs($reflectionMethod, $arguments)
            );
        }
        throw new BadMethodCallException('Unable to call method: ' . $method);
    }

    /**
     * Call the closure.
     *
     * @param Closure|string $function  function
     * @param array          $arguments parameter list (associative array)
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ReflectionException
     */
    public function callFunc(string|Closure $function, array $arguments = [])
    {
        $reflectFunction = new ReflectionFunction($function);

        return $reflectFunction->invokeArgs(
            $this->getFuncArgs($reflectFunction, $arguments)
        );
    }

    /**
     * Get the parameters of the constructor.
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function getConstructorArgs(ReflectionClass $reflectionClass, array $arguments = []): array
    {
        if (is_null($constructor = $reflectionClass->getConstructor())) {
            return $arguments;
        }
        if ($reflectionClass->isInstantiable()) {
            return $this->getFuncArgs($constructor, $arguments);
        }
        throw new ContainerException('Cannot initialize class: ' . $reflectionClass->getName(), 599);
    }

    /**
     * @param ReflectionFunctionAbstract $reflectionFunction reflection method
     * @param array                      $arguments          parameter list (associative array)
     *
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function getFuncArgs(ReflectionFunctionAbstract $reflectionFunction, array $arguments = []): array
    {
        $funcArgs = [];
        foreach ($reflectionFunction->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $arguments)) {
                $funcArgs[] = &$arguments[$name];
                unset($arguments[$name]);
            } else {
                $type = $parameter->getType();
                if (is_null($type)
                    || ($type instanceof ReflectionNamedType && $type->isBuiltin())
                    || $type instanceof ReflectionUnionType
                    || ($typeName = $type->getName()) === 'Closure'
                ) {
                    if (! $parameter->isVariadic()) {
                        $funcArgs[] = $parameter->isOptional()
                            ? $parameter->getDefaultValue()
                            : throw new ContainerException(sprintf('Missing parameter `%s`', $name));
                    } else {
                        // variable arguments at the end
                        array_push($funcArgs, ...array_values($arguments));
                        break;
                    }
                } else {
                    try {
                        $funcArgs[] = $this->make($typeName);
                    } catch (ReflectionException|ContainerExceptionInterface $e) {
                        $funcArgs[] = $parameter->isOptional() ? $parameter->getDefaultValue() : throw $e;
                    }
                }
            }
        }

        return $funcArgs;
    }
}
