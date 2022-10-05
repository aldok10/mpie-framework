<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop\Annotation;

use Attribute;
use Mpie\Aop\Contract\PropertyAnnotation;
use Mpie\Aop\Exception\PropertyHandleException;
use Mpie\Di\Reflection;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Throwable;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject implements PropertyAnnotation
{
    /**
     * @param string $id type of injection
     */
    public function __construct(
        protected string $id = ''
    ) {
    }

    public function handle(object $object, string $property): void
    {
        try {
            $reflectionProperty = Reflection::property($object::class, $property);
            if ((! is_null($type = $reflectionProperty->getType()) && $type = $type->getName()) || $type = $this->id) {
                $reflectionProperty->setAccessible(true); // Compatible with PHP8.0
                $reflectionProperty->setValue($object, $this->getBinding($type));
            }
        } catch (Throwable $e) {
            throw new PropertyHandleException('Property assign failed. ' . $e->getMessage());
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function getBinding(string $type): object
    {
        return make($type);
    }
}
