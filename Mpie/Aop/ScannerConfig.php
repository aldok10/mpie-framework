<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Aop;

use Mpie\Utils\Traits\AutoFillProperties;

class ScannerConfig
{
    use AutoFillProperties;

    protected bool $cache = false;

    protected array $scanDirs = [];

    protected array $collectors = [];

    protected string $runtimeDir = '';

    public function __construct(array $options)
    {
        $this->fillProperties($options);
    }

    public function isCache(): bool
    {
        return $this->cache;
    }

    public function getScanDirs(): array
    {
        return $this->scanDirs;
    }

    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function getRuntimeDir(): string
    {
        return rtrim($this->runtimeDir, '/\\');
    }
}
