<?php

namespace Curfle\Routing;

use Curfle\Agreements\Container\Container;
use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\Http\Dispatchable\HttpNotFoundException;
use Curfle\Support\Exceptions\Http\MiddlewareNotFoundException;
use Curfle\Support\Str;

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
    public static array $verbs = ["GET", "HEAD", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"];

    /**
     * The group options when calling the ->group(...) function.
     *
     * @var array
     */
    public array $groupOptions = [
        "prefix" => "",
        "middleware" => []
    ];

    /**
     * The application's global HTTP middleware stack.
     * These middlewares are run during every request.
     *
     * @var array
     */
    protected array $middleware = [];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected array $middlewareGroups = [];

    /**
     * The application's route middleware.
     * These middlewares may be assigned to middleware groups or used individually.
     *
     * @var array
     */
    protected array $routeMiddleware = [];


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
     * @param callable|array|null $action
     * @return Route
     */
    public function get(string $uri, callable|array|null $action = null): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param callable|array|null $action
     * @return Route
     */
    public function post(string $uri, callable|array|null $action = null): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param string $uri
     * @param callable|array|null $action
     * @return Route
     */
    public function put(string $uri, callable|array|null $action = null): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a new PATCH route with the router.
     *
     * @param string $uri
     * @param callable|array|null $action
     * @return Route
     */
    public function patch(string $uri, callable|array|null $action = null): Route
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param string $uri
     * @param callable|array|null $action
     * @return Route
     */
    public function delete(string $uri, callable|array|null $action = null): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string $uri
     * @param callable|array|null $action
     * @return Route
     */
    public function options(string $uri, callable|array|null $action = null): Route
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * Register a new route within the given methods with the router.
     *
     * @param array $methods
     * @param string $uri
     * @param callable|array|null $action
     * @return Route
     */
    public function methods(array $methods, string $uri, callable|array|null $action = null): Route
    {
        return $this->addRoute($methods, $uri, $action);
    }

    /**
     * Register a new route responding to all verbs.
     *
     * @param string $uri
     * @param callable|array|null $action
     * @return Route
     */
    public function any(string $uri, callable|array|null $action = null): Route
    {
        return $this->addRoute(self::$verbs, $uri, $action);
    }

    /**
     * Register a new Fallback route with the router.
     *
     * @param callable|array|null $action
     * @return Route
     */
    public function fallback(callable|array|null $action): Route
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
        return $this->addRoute(self::$verbs, $uri, function (Response $response) use ($code, $target) {
            $response
                ->setStatusCode($code)
                ->setHeader("Location", $target)
                ->setContent("");
        });
    }

    /**
     * Sets the group prefix option.
     *
     * @param string $prefix
     * @return $this
     */
    public function prefix(string $prefix): static
    {
        $this->groupOptions["prefix"] = $prefix;
        return $this;
    }

    /**
     * Sets the group middleware option and assigns the routes a group of middlewares.
     *
     * @param string $middleware
     * @return $this
     */
    public function middleware(string $middleware): static
    {
        $this->groupOptions["middleware"] = $this->middlewareGroups[$middleware] ?? [];
        return $this;
    }

    /**
     * Resets the group options.
     *
     * @return $this
     */
    private function clearGroupOptions(): static
    {
        $this->groupOptions["prefix"] = "";
        $this->groupOptions["middleware"] = [];
        return $this;
    }

    /**
     * Sets the group middleware option.
     *
     * @param mixed $routes
     * @return $this
     */
    public function group(mixed $routes): static
    {
        if (is_callable($routes))
            $routes();

        return $this->clearGroupOptions();
    }

    /**
     * Add a route to the route collection.
     *
     * @param array|string $methods
     * @param string $uri
     * @param callable|array|null $action
     * @return Route
     */
    private function addRoute(array|string $methods, string $uri, callable|array|null $action): Route
    {
        // create route and add to route collector
        $route = $this->routeCollector->add(
            $this->createRoute(
                $methods,
                $this->groupOptions["prefix"] . $uri,
                $action
            )
        );

        // assign middleware if wanted
        foreach($this->groupOptions["middleware"] as $middleware){
            $route->middleware($middleware);
        }

        return $route;
    }

    /**
     * Create a new route instance.
     *
     * @param array|string $methods
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    private function createRoute(array|string $methods, string $uri, callable|array|null $action): Route
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
     * @throws HttpNotFoundException
     * @throws MiddlewareNotFoundException
     */
    public function resolve(Request $request): Response
    {
        // try to find route
        foreach ($this->routeCollector->all() as $route) {
            if ($route->matches($request->method(), $request->uri())) {
                // add inputs to request
                foreach ($route->getMatchedParameters() as $name => $value) {
                    $request->addInput($name, $value);
                }

                // call middlewares
                foreach ($this->middleware as $middleware) {
                    $this->container->call("$middleware@handle");
                }

                // resolve route
                return $route->resolve($request);
            }
        }

        throw new HttpNotFoundException();
    }

    /**
     * Registeres a new middleware.
     *
     * @param string $middleware
     * @return $this
     */
    public function addGlobalMiddleware(string $middleware): static
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Registeres a new middleware group.
     *
     * @param string $group
     * @param array $middlewareGroup
     * @return $this
     */
    public function groupMiddleware(string $group, array $middlewareGroup): static
    {
        $this->middlewareGroups[$group] = $middlewareGroup;
        return $this;
    }

    /**
     * Registeres a new middleware group.
     *
     * @param string $alias
     * @param string $middleware
     * @return $this
     */
    public function aliasMiddleware(string $alias, string $middleware): static
    {
        $this->routeMiddleware[$alias] = $middleware;
        return $this;
    }

    /**
     * Returns the middleware by its alias or classname.
     *
     * @param string $alias
     * @return Middleware
     * @throws MiddlewareNotFoundException
     */
    public function getMiddleware(string $alias): Middleware
    {
        $parts = Str::split($alias, ":");
        $alias = $parts[0];
        $parameters = array_slice($parts, 1);

        if (array_key_exists($alias, $this->routeMiddleware))
            return $this->container->resolve($this->routeMiddleware[$alias])
                ->setParameters($parameters);

        if (class_exists($alias))
            return $this->container->resolve($alias)
                ->setParameters($parameters);

        throw new MiddlewareNotFoundException("The middleware [$alias] could not be found.");
    }
}