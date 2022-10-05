<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Utils\Traits;

use function Mpie\Utils\tap;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
trait Tappable
{
    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param  null|callable $callback
     * @return mixed
     */
    public function tap($callback = null)
    {
        return tap($this, $callback);
    }
}
