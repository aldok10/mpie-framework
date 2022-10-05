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

class PhpEngine implements ViewEngineInterface
{
    use AutoFillProperties;

    protected string $suffix = '.php';

    protected string $path;

    public function __construct(array $config)
    {
        $this->fillProperties($config);
    }

    public function render(string $template, array $arguments = []): void
    {
        $this->renderView($template, $arguments);
    }

    protected function renderView(): void
    {
        extract(func_get_arg(1));
        include $this->findViewFile(func_get_arg(0));
    }

    protected function findViewFile(string $view): string
    {
        return sprintf('%s/%s%s', rtrim($this->path, '/'), trim($view, '/'), $this->suffix);
    }
}
