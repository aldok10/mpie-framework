<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Routing\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    /**
     * @param string $prefix      prefix
     * @param array  $middlewares middleware
     * @param array  $patterns    Parameter rules
     */
    public function __construct(
        public string $prefix = '/',
        public array $middlewares = [],
        public array $patterns = [],
    ) {
    }
}
