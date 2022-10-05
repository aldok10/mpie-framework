<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\View\Engine;

use Mpie\Utils\Traits\AutoFillProperties;
use Mpie\View\Contract\ViewEngineInterface;
use Mpie\View\Engine\Blade\Compiler;
use Mpie\View\Exception\ViewNotExistException;

use function func_get_arg;

class BladeEngine implements ViewEngineInterface
{
    use AutoFillProperties;

    /**
     * Cache.
     */
    protected bool $cache = false;

    /**
     * Suffix.
     */
    protected string $suffix = '.blade.php';

    /**
     * build directory.
     */
    protected string $compileDir;

    protected string $path;

    public function __construct(array $config)
    {
        $this->fillProperties($config);
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isCache(): bool
    {
        return $this->cache;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function getCompileDir(): string
    {
        return $this->compileDir;
    }

    /**
     * @throws ViewNotExistException
     */
    public function render(string $template, array $arguments = [])
    {
        $this->renderView($template, $arguments);
    }

    /**
     * @throws ViewNotExistException
     */
    protected function renderView(): void
    {
        extract(func_get_arg(1));
        include (new Compiler($this))->compile(func_get_arg(0));
    }
}
