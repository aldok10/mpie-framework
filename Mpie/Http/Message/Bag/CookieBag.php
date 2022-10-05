<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Message\Bag;

class CookieBag extends ParameterBag
{
    protected array $map = [];

    public function replace(array $parameters = [])
    {
        $this->parameters = $parameters;
        foreach ($parameters as $key => $parameter) {
            $this->map[strtoupper($key)] = $key;
        }
    }

    public function get(string $key, $default = null): mixed
    {
        return parent::get(strtoupper($key), $default);
    }

    public function set(string $key, $value)
    {
        parent::set(strtoupper($key), $value);
    }

    public function has(string $key): bool
    {
        return parent::has(strtoupper($key));
    }

    public function remove(string $key)
    {
        parent::remove(strtoupper($key));
    }
}
