<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\View;

use Mpie\Config\Contract\ConfigInterface;
use Mpie\View\Contract\ViewEngineInterface;

class ViewFactory
{
    protected ViewEngineInterface $engine;

    /**
     * Renderer constructor.
     */
    public function __construct(ConfigInterface $config)
    {
        $engine       = $config->get('view.engine');
        $config       = $config->get('view.config', []);
        $this->engine = new $engine($config);
    }

    public function getRenderer(): Renderer
    {
        return new Renderer($this->engine);
    }

    public function render(string $template, array $arguments = []): string
    {
        return $this->getRenderer()->render($template, $arguments);
    }
}
