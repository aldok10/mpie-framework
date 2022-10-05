<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Database\Query;

/**
 * @mixin Builder
 */
class Join
{
    public array $on = [];

    public function __construct(
        protected Builder $builder,
        public string $table,
        public ?string $alias = null,
        public string $league = 'INNER JOIN'
    ) {
    }

    /**
     * @param $method
     * @param $args
     *
     * @return Builder
     */
    public function __call($method, $args)
    {
        return $this->builder->{$method}(...$args);
    }

    /**
     * @param $first
     * @param $last
     */
    public function on($first, $last, string $operator = '='): Builder
    {
        $this->on = [$first, $operator, $last];

        return $this->builder;
    }
}
