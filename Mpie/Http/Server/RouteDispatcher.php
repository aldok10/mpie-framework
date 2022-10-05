<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Server;

use Mpie\Http\Server\Contract\RouteDispatcherInterface;
use Mpie\Routing\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use RuntimeException;

class RouteDispatcher implements RouteDispatcherInterface
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        if ($route = $request->getAttribute(Route::class)) {
            $parameters            = $route->getParameters();
            $parameters['request'] = $request;
            return call($route->getAction(), $parameters);
        }
        throw new RuntimeException('No route found in the request context', 404);
    }
}
