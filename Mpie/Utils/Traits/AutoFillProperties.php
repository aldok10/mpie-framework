<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Utils\Traits;

trait AutoFillProperties
{
    /**
     * Populate properties with an array.
     */
    protected function fillProperties(array $properties, bool $force = false): void
    {
        foreach ($properties as $key => $value) {
            if ($force || property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
