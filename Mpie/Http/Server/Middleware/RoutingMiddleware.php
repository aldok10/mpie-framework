<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Server\Middleware;

use Mpie\Routing\Exception\MethodNotAllowedException;
use Mpie\Routing\Exception\RouteNotFoundException;
use Mpie\Routing\Route;
use Mpie\Routing\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected RouteCollector $routeCollector
    ) {
    }

    /**
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route   = $this->routeCollector->resolveRequest($request);
        $request = $request->withAttribute(Route::class, $route);

        return $handler->use(...$route->getMiddlewares())->handle($request);
    }
}
