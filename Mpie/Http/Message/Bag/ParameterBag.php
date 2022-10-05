<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Message\Bag;

class ParameterBag
{
    protected array $parameters = [];

    public function __construct(array $parameters = [])
    {
        $this->replace($parameters);
    }

    public function get(string $key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }

    public function set(string $key, $value)
    {
        $this->parameters[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function remove(string $key)
    {
        unset($this->parameters[$key]);
    }

    public function replace(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function all(): array
    {
        return $this->parameters;
    }
}
