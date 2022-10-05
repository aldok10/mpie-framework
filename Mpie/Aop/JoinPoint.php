<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop;

use ArrayObject;
use Closure;

class JoinPoint
{
    /**
     * @param string      $class      ClassName
     * @param string      $method     Methods
     * @param ArrayObject $parameters List of parameters passed by the current method [index array]
     */
    public function __construct(
        public string $class,
        public string $method,
        public ArrayObject $parameters,
        protected Closure $callback
    ) {
    }

    /**
     * 执行代理方法.
     */
    public function process(): mixed
    {
        return call_user_func_array($this->callback, $this->parameters->getArrayCopy());
    }
}
