<?php

namespace Curfle\Routing;

use Curfle\Agreements\Container\Container;
use Curfle\Essence\Application;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\Http\NotFoundHttpException;

class Router
{
    /**
     * The service container instance.
     *
     * @var Container
     */

    private Container $container;

    /**
     * All available Routes in router.
     *
     * @var RouteCollector
     */
    private RouteCollector $routeCollector;

    /**
     * All the verbs supported by the router.
     *
     * @var string[]
     */
    public static array $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];


    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->routeCollector = new RouteCollector();
    }

    /**
     * Register a new GET route with the router.
     *
     * @param string $uri
     * @param callable|array|string|null $action
     * @return Route
     */
    public function get(string $uri, callable|array|string $action = null): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param callable|array|string|null $action
     * @return Route
     */
    public function post(string $uri, callable|array|string $action = null): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param string $uri
     * @param callable|array|string|null $action
     * @return Route
     */
    public function put(string $uri, callable|array|string $action = null): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a new PATCH route with the router.
     *
     * @param string $uri
     * @param callable|array|string|null $action
     * @return Route
     */
    public function patch(string $uri, callable|array|string $action = null): Route
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param string $uri
     * @param callable|array|string|null $action
     * @return Route
     */
    public function delete(string $uri, callable|array|string $action = null): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string $uri
     * @param callable|array|string|null $action
     * @return Route
     */
    public function options(string $uri, callable|array|string $action = null): Route
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * Register a new route responding to all verbs.
     *
     * @param string $uri
     * @param callable|array|string|null $action
     * @return Route
     */
    public function any(string $uri, callable|array|string $action = null): Route
    {
        return $this->addRoute(self::$verbs, $uri, $action);
    }

    /**
     * Register a new Fallback route with the router.
     *
     * @param callable|array|string|null $action
     * @return Route
     */
    public function fallback(callable|array|string|null $action): Route
    {
        $placeholder = 'fallbackPlaceholder';

        return $this->addRoute(
            'GET', "{{$placeholder}}", $action
        )->where($placeholder, '.*');
    }

    /**
     * Create a redirect from one url to another.
     *
     * @param string $uri
     * @param string $target
     * @param int $code
     * @return Route
     */
    public function redirect(string $uri, string $target, int $code = 302): Route
    {
        return $this->addRoute(self::$verbs, $uri, function() use($code, $target){
            return new Response("", $code, ["Location" => $target]);
        });
    }

    /**
     * Add a route to the route collection.
     *
     * @param array|string $methods
     * @param string $uri
     * @param callable|array|string|null $action
     * @return Route
     */
    private function addRoute(array|string $methods, string $uri, callable|array|string|null $action): Route
    {
        return $this->routeCollector->add($this->createRoute($methods, $uri, $action));
    }

    /**
     * Create a new route instance.
     *
     * @param array|string $methods
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    private function createRoute(array|string $methods, string $uri, mixed $action): Route
    {
        return (new Route($methods, $uri, $action))
            ->setRouter($this)
            ->setContainer($this->container);
    }

    /**
     * Registers a file containing routes.
     *
     * @param string $filename
     * @return $this
     */
    public function registerRouteFile(string $filename): static
    {
        require $filename;
        return $this;
    }

    /**
     * Resolves a Request with a Response.
     *
     * @param Request $request
     * @return Response
     * @throws NotFoundHttpException
     */
    public function resolve(Request $request): Response
    {
        foreach ($this->routeCollector->all() as $route) {
            if ($route->matches($request->method(), $request->uri())) {
                foreach ($route->getMatchedParameters() as $name => $value) {
                    $request->addInput($name, $value);
                }
                return $route->resolve($request);
            }
        }

        throw new NotFoundHttpException("Not found", 404);
    }
}