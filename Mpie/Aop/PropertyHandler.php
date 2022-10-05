<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop;

use Mpie\Aop\Collector\PropertyAnnotationCollector;

trait PropertyHandler
{
    protected bool $__propertyHandled = false;

    protected function __handleProperties(): void
    {
        if (! $this->__propertyHandled) {
            foreach (PropertyAnnotationCollector::getByClass(self::class) as $property => $attributes) {
                foreach ($attributes as $attribute) {
                    $attribute->handle($this, $property);
                }
            }
            $this->__propertyHandled = true;
        }
    }
}
