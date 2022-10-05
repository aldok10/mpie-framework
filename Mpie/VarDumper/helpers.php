<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

use Mpie\VarDumper\Abort;

if (function_exists('d') === false) {
    /**
     * @throws Abort
     */
    function d(...$vars)
    {
        throw new Abort($vars);
    }
}

if (function_exists('dd') === false) {
    /**
     * Use `d` instead of `dd`.
     *
     * @throws ErrorException
     * @deprecated
     */
    function dd(...$vars)
    {
        throw new ErrorException('Use `d` instead of `dd`');
    }
}
