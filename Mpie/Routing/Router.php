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
use InvalidArgumentException;
use Mpie\Http\Message\Contract\RequestMethodInterface;

use function array_unique;
use function sprintf;

class Router
{
    /**
     * @param string $prefix      url prefix
     * @param array  $patterns    parameter rules
     * @param array  $middlewares middleware
     * @param string $namespace   Namespaces
     */
    public function __construct(
        protected string $prefix = '',
        protected array $patterns = [],
        protected string $namespace = '',
        protected array $middlewares = [],
        protected ?RouteCollector $routeCollector = null
    ) {
        $this->routeCollector ??= new RouteCollector();
    }

    /**
     * Allow almost all methods.
     * For example: $router->any('/', [IndexController@class, 'index']).
     *
     * @param string               $path   the request path
     * @param array|Closure|string $action the handling method
     */
    public function any(string $path, array|Closure|string $action): Route
    {
        return $this->request($path, $action, [
            RequestMethodInterface::METHOD_GET,
            RequestMethodInterface::METHOD_HEAD,
            RequestMethodInterface::METHOD_POST,
            RequestMethodInterface::METHOD_OPTIONS,
            RequestMethodInterface::METHOD_PUT,
            RequestMethodInterface::METHOD_PATCH,
            RequestMethodInterface::METHOD_DELETE,
        ]);
    }

    /**
     * Method PATCH.
     */
    public function patch(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, [RequestMethodInterface::METHOD_PATCH]);
    }

    /**
     * Method OPTIONS.
     */
    public function options(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, [RequestMethodInterface::METHOD_OPTIONS]);
    }

    /**
     * Method PUT.
     */
    public function put(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, [RequestMethodInterface::METHOD_PUT]);
    }

    /**
     * Method DELETE.
     */
    public function delete(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, [RequestMethodInterface::METHOD_DELETE]);
    }

    /**
     * Method POST.
     */
    public function post(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, [RequestMethodInterface::METHOD_POST]);
    }

    /**
     * Method GET.
     */
    public function get(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, [RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_HEAD]);
    }

    /**
     * Restful routing.
     */
    public function rest(string $uri, string $controller): RestRouter
    {
        return new RestRouter(
            $this->routeCollector,
            $this->prefix . $uri,
            $this->formatController($controller),
            $this->middlewares,
            $this->patterns,
        );
    }

    /**
     * Allow multi request methods.
     */
    public function request(string $path, array|Closure|string $action, array $methods = ['GET', 'HEAD', 'POST']): Route
    {
        if (is_string($action)) {
            $action = str_contains($action, '@')
                ? explode('@', $this->formatController($action), 2)
                : [$this->formatController($action), '__invoke'];
        }
        if ($action instanceof Closure || count($action) === 2) {
            if (is_array($action)) {
                [$controller, $action] = $action;
                $action                = [$this->formatController($controller), $action];
            }
            return $this->routeCollector->addRoute(new Route($methods, $this->prefix . $path, $action, $this->patterns, $this->middlewares));
        }
        throw new InvalidArgumentException('Invalid route action: ' . $path);
    }

    /**
     * Grouping routing.
     */
    public function group(Closure $group): void
    {
        $group($this);
    }

    /**
     * Add middleware.
     */
    public function middleware(string ...$middlewares): Router
    {
        $new              = clone $this;
        $new->middlewares = array_unique([...$this->middlewares, ...$middlewares]);

        return $new;
    }

    /**
     * Single variable rule.
     */
    public function where(string $name, string $pattern): Router
    {
        $new                  = clone $this;
        $new->patterns[$name] = $pattern;

        return $new;
    }

    /**
     * Prefix.
     */
    public function prefix(string $prefix): Router
    {
        $new         = clone $this;
        $new->prefix = $this->prefix . $prefix;

        return $new;
    }

    /**
     * Namespaces.
     */
    public function namespace(string $namespace): Router
    {
        $new            = clone $this;
        $new->namespace = sprintf('%s\\%s', $this->namespace, trim($namespace, '\\'));

        return $new;
    }

    /**
     * Route collector.
     */
    public function getRouteCollector(): RouteCollector
    {
        return $this->routeCollector;
    }

    /**
     * Splicing namespaces and controllers together.
     */
    protected function formatController(string $controller): string
    {
        return trim($this->namespace . '\\' . ltrim($controller, '\\'), '\\');
    }
}
