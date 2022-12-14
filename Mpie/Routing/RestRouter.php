<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Routing;

class RestRouter
{
    /**
     * Rest routing rules.
     *
     * @var array|array[]
     */
    protected static array $maps = [
        'index'  => [['GET', 'HEAD'], '/%s'],
        'show'   => [['GET', 'HEAD'], '/%s/{id}'],
        'store'  => [['POST'], '/%s'],
        'update' => [['PUT', 'PATCH'], '/%s/{id}'],
        'delete' => [['DELETE'], '/%s/{id}'],
    ];

    protected Route $indexRoute;

    protected Route $showRoute;

    protected Route $storeRoute;

    protected Route $updateRoute;

    protected Route $deleteRoute;

    public function __construct(
        protected RouteCollector $routeCollector,
        protected string $uri,
        protected string $controller,
        protected array $middlewares = [],
        protected array $patterns = [],
    ) {
        foreach (static::$maps as $action => $map) {
            [$methods, $path] = $map;
            $property         = $action . 'Route';
            $this->routeCollector->addRoute($this->{$property} = new Route(
                $methods,
                sprintf($path, $uri),
                [$this->controller, $action],
                $this->patterns,
                $this->middlewares,
            ));
        }
    }

    public function getIndexRoute(): Route
    {
        return $this->indexRoute;
    }

    public function getShowRoute(): Route
    {
        return $this->showRoute;
    }

    public function getStoreRoute(): Route
    {
        return $this->storeRoute;
    }

    public function getUpdateRoute(): Route
    {
        return $this->updateRoute;
    }

    public function getDeleteRoute(): Route
    {
        return $this->deleteRoute;
    }
}
