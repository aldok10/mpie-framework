<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\View;

use Mpie\View\Contract\ViewEngineInterface;

class Renderer
{
    public function __construct(
        protected ViewEngineInterface $engine,
        protected array $arguments = []
    ) {
    }

    public function assign(string $name, mixed $value): void
    {
        $this->arguments[$name] = $value;
    }

    public function render(string $template, array $arguments = []): string
    {
        ob_start();
        echo (string) $this->engine->render($template, array_merge($this->arguments, $arguments));
        return (string) ob_get_clean();
    }
}
