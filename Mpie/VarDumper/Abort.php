<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\VarDumper;

use RuntimeException;

class Abort extends RuntimeException
{
    public array $vars;

    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }
}
