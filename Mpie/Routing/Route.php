<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Routing;

use Closure;

use function preg_replace_callback;
use function sprintf;
use function trim;

class Route
{
    /**
     * Default rules.
     */
    protected const DEFAULT_VARIABLE_REGEX = '[^\/]+';

    /**
     * Variable regularization.
     */
    protected const VARIABLE_REGEX = '\{\s*([a-zA-Z_][a-zA-Z0-9_-]*)\s*(?::\s*([^{}]*(?:\{(?-1)\}[^{}]*)*))?\}';

    protected string $compiledPath = '';

    /**
     * Route parameter.
     */
    protected array $parameters = [];

    /**
     * Initialization data.
     */
    public function __construct(
        protected array $methods,
        protected string $path,
        protected Closure|array $action,
        protected array $patterns = [],
        protected array $middlewares = []
    ) {
        $compiledPath       = preg_replace_callback(sprintf('#%s#', self::VARIABLE_REGEX), function ($matches) {
            $name           = $matches[1];
            if (isset($matches[2])) {
                $this->patterns[$name] = $matches[2];
            }
            $this->setParameter($name, null);
            return sprintf('(?P<%s>%s)', $name, $this->getPattern($name));
        }, $this->path      = '/' . trim($this->path, '/'));
        $this->compiledPath = sprintf('#^%s$#iU', $compiledPath);
    }

    /**
     * Get route parameter rules.
     */
    public function getPattern(string $key): string
    {
        return $this->getPatterns()[$key] ?? static::DEFAULT_VARIABLE_REGEX;
    }

    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * return the compiled regex.
     */
    public function getCompiledPath(): string
    {
        return $this->compiledPath;
    }

    /**
     * Set a single route parameter.
     */
    public function setParameter(string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Set routing parameters, all.
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Get a single route parameter.
     */
    public function getParameter(string $name): ?string
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * Get all route parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set up middleware.
     */
    public function middleware(string ...$middlewares): Route
    {
        $this->middlewares = array_unique([...$this->middlewares, ...$middlewares]);

        return $this;
    }

    /**
     * Excluded middleware.
     */
    public function withoutMiddleware(string $middleware): Route
    {
        if (($key = array_search($middleware, $this->middlewares)) !== false) {
            unset($this->middlewares[$key]);
        }

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getAction(): array|string|Closure
    {
        return $this->action;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
