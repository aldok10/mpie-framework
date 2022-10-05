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

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AspectConfig
{
    /**
     * @param string       $class   ClassName
     * @param array|string $methods Methods
     * @param array        $params  Params
     */
    public function __construct(
        public string $class,
        public string|array $methods = '*',
        public array $params = []
    ) {
    }
}
