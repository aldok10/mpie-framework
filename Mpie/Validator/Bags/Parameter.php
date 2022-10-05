<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Validator\Bags;

class Parameter
{
    protected array $items = [];

    /**
     * @return $this
     */
    public function push(string $error): static
    {
        $this->items[] = $error;

        return $this;
    }

    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function all(): array
    {
        return $this->items;
    }
}
