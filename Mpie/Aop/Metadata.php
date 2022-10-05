<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop;

class Metadata
{
    /**
     * @param string $className      class name
     * @param bool   $hasConstructor Is there a constructor
     */
    public function __construct(
        public string $className,
        public bool $hasConstructor = false
    ) {
    }
}
