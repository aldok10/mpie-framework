<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Database\Query;

class Expression
{
    public function __construct(
        public string $expression
    ) {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->expression;
    }
}
