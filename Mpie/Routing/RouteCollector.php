<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Routing;

use Mpie\Http\Message\Contract\StatusCodeInterface;
use Mpie\Routing\Exception\MethodNotAllowedException;
use Mpie\Routing\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function preg_match;

class RouteCollector
{
    /**
     * All routes not grouped.
     *
     * @var array<string, Route[]>
     */
    protected array $routes = [];

    /**
     * Add a route.
     */
    public function addRoute(Route $route): Route
    {
        foreach ($route->getMethods() as $method) {
            $this->routes[$method][] = $route;
        }
        return $route;
    }

    /**
     * All.
     *
     * @return array<string, Route[]>
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * Routes are resolved using the ServerRequestInterface object.
     *
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function resolveRequest(ServerRequestInterface $request): Route
    {
        $path   = '/' . trim($request->getUri()->getPath(), '/');
        $method = $request->getMethod();
        return $this->resolve($method, $path);
    }

    /**
     * Resolve route using request method and request path.
     */
    public function resolve(string $method, string $path): Route
    {
        $routes = $this->routes[$method] ?? throw new MethodNotAllowedException('Method not allowed: ' . $method, StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED);
        foreach ($routes as $route) {
            if (($compiledPath = $route->getCompiledPath()) && preg_match($compiledPath, $path, $match)) {
                $resolvedRoute = clone $route;
                if (! empty($match)) {
                    foreach ($route->getParameters() as $key => $value) {
                        if (array_key_exists($key, $match)) {
                            $resolvedRoute->setParameter($key, $match[$key]);
                        }
                    }
                }
                return $resolvedRoute;
            }
        }

        throw new RouteNotFoundException('Not Found', StatusCodeInterface::STATUS_NOT_FOUND);
    }
}
