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
use Mpie\Http\Server\Event\OnRequest;
use Mpie\Routing\Exception\RouteNotFoundException;
use Mpie\Routing\Route;
use Mpie\Routing\RouteCollector;
use Mpie\Routing\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

class Kernel
{
    /**
     * Global middleware.
     */
    protected array $middlewares = [];

    /**
     * @param ?ContainerInterface       $container       Container
     * @param ?RouteCollector           $routeCollector  Route collector
     * @param ?RouteDispatcherInterface $routeDispatcher Route dispatcher
     * @param ?EventDispatcherInterface $eventDispatcher Event scheduler
     */
    final public function __construct(
        protected ?ContainerInterface $container = null,
        protected ?RouteCollector $routeCollector = new RouteCollector(),
        protected ?RouteDispatcherInterface $routeDispatcher = null,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    ) {
        $this->map(new Router(routeCollector: $routeCollector));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException|RouteNotFoundException
     */
    public function through(ServerRequestInterface $request): ResponseInterface
    {
        $event           = new OnRequest($request);
        $response        = (new RequestHandler($this->container, $this->routeDispatcher, $this->middlewares))->handle($request);
        $event->response = $response;
        $this->eventDispatcher?->dispatch($event);
        return $response;
    }

    /**
     * Add middleware.
     */
    public function use(string ...$middleware): static
    {
        array_push($this->middlewares, ...$middleware);
        return $this;
    }

    /**
     * @return array<string,Route[]>
     */
    public function getAllRoutes(): array
    {
        return $this->routeCollector->all();
    }

    /**
     * Route registration.
     */
    protected function map(Router $router): void
    {
    }
}
