<?php

namespace Curfle\Routing;

class RouteCollector
{
    /**
     * All registered routes.
     *
     * @var Route[]
     */
    private array $routes = [];

    /**
     * Adds a route instance to the collection.
     *
     * @param Route $route
     * @return Route
     */
    public function add(Route $route) : Route
    {
        $this->routes[] = $route;
        return $route;
    }

    /**
     * Returns all routes.
     *
     * @return Route[]
     */
    public function all(): array{
        return $this->routes;
    }
}