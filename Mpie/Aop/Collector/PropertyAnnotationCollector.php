<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop\Collector;

use Mpie\Aop\Contract\PropertyAnnotation;

class PropertyAnnotationCollector extends AbstractCollector
{
    protected static array $container = [];

    /**
     * Collect property annotations.
     */
    public static function collectProperty(string $class, string $property, object $attribute): void
    {
        if (self::isValid($attribute)) {
            self::$container[$class][$property][] = $attribute;
        }
    }

    /**
     * Returns all properties and annotations of the class containing the properties.
     */
    public static function getByClass(string $class): array
    {
        return self::$container[$class] ?? [];
    }

    /**
     * Returns an annotation for a property of a class.
     *
     * @return PropertyAnnotation[]
     */
    public static function getByProperty(string $class, string $property): array
    {
        return self::$container[$class][$property] ?? [];
    }

    /**
     * 返回收集过的类.
     *
     * @return string[]
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }

    /**
     * 是否可以被收集.
     */
    protected static function isValid(object $attribute): bool
    {
        return $attribute instanceof PropertyAnnotation;
    }
}
