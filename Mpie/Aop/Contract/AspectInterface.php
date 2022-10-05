<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop\Contract;

use Closure;
use Mpie\Aop\JoinPoint;

interface AspectInterface
{
    public function process(JoinPoint $joinPoint, Closure $next): mixed;
}
