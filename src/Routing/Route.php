<?php

namespace Curfle\Routing;

use Curfle\Agreements\Container\Container;
use Curfle\Http\Middleware;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\Http\MiddlewareNotFoundException;
use Curfle\View\View;

class Route
{
    /**
     * The routes' container reference.
     *
     * @var Container
     */
    private Container $container;

    /**
     * The routes' router reference.
     *
     * @var Router
     */
    private Router $router;

    /**
     * The routes' accepted methods.
     *
     * @var array
     */
    private array $methods = [];

    /**
     * The routes' uri.
     *
     * @var string
     */
    private string $uri;

    /**
     * The route's action when route matched.
     *
     * @var mixed
     */
    private mixed $action;

    /**
     * The conditions for the parameter.
     *
     * @var array
     */
    private array $where = [];

    /**
     * The matched parameters from the last matches() call.
     *
     * @var array|null
     */
    private ?array $matchedParameters = null;

    /**
     * The middlewares assigned to the route
     *
     * @var string[]
     */
    private array $middleware = [];

    /**
     * @param array|string $methods
     * @param string $uri
     * @param mixed $action
     */
    public function __construct(array|string $methods, string $uri, mixed $action)
    {
        $this->uri = $uri;
        $this->methods = is_array($methods) ? $methods : [$methods];
        $this->action = $action;
    }

    /**
     * Returns if the uri and method match this route or not.
     *
     * @param string $method
     * @param string $uri
     * @return bool
     */
    public function matches(string $method, string $uri): bool
    {
        return in_array($method, $this->methods) && $this->getMatches($uri) !== null;
    }

    /**
     * Returns the matched parameters from the last ->matches(...) call.
     *
     * @return array|null
     */
    public function getMatchedParameters(): ?array
    {
        return $this->matchedParameters;
    }

    /**
     * Returns all matches of a uri and its parameters against this route.
     *
     * @param string $uri
     * @return array|null
     */
    private function getMatches(string $uri): ?array
    {
        $parameters = [];

        // find parameter matches
        $matches = [];
        $compiledUri = $this->compileUri();
        preg_match_all($compiledUri, $uri, $matches, PREG_OFFSET_CAPTURE);

        // if uri does not match -> return null
        if (empty($matches[0]))
            return null;

        // sort where conditions in order of uri
        $whereMatches = [];
        $parameterRegex = '/{([a-z]|[A-Z]|[0-9])*}/m';
        preg_match_all($parameterRegex, $this->uri, $whereMatches, PREG_OFFSET_CAPTURE);
        foreach ($whereMatches[0] as $i => $match) {
            $name = substr($match[0], 1, -1);
            $parameters[$name] = $matches[$i + 1][0][0];
        }

        // cache params
        $this->matchedParameters = $parameters;

        return $parameters;
    }

    /**
     * Compiles the routes' uri.
     *
     * @return string
     */
    private function compileUri(): string
    {
        $uri = $this->uri;
        foreach ($this->where as $parameter => $regex) {
            $uri = str_replace("{{$parameter}}", "($regex)", $uri);
        }

        $uri = str_replace("/", "\/", $uri);

        return "/^$uri$/m";
    }

    /**
     * Adds a regex condition to a parameter.
     *
     * @param string $parameter
     * @param string $regex
     * @return $this
     */
    public function where(string $parameter, string $regex): static
    {
        $this->where[$parameter] = $regex;
        return $this;
    }

    /**
     * Adds a middleware by name or classname to the route.
     *
     * @param string $name
     * @return $this
     */
    public function middleware(string $name): static
    {
        $this->middleware[] = $name;
        return $this;
    }

    /**
     * Resolves a request with a response based on its action.
     * @param Request $request
     * @return Response
     * @throws MiddlewareNotFoundException
     */
    public function resolve(Request $request): Response
    {
        // pass request through the middleware stack
        foreach ($this->middleware as $middleware) {
            $this->router->getMiddleware($middleware)->handle($request);
        }

        // resolve the request
        $response = $this->action;

        // call the resolver
        if (is_callable($this->action))
            $response = $this->container->call($this->action);

        // if response is null, default it to the apps' singleton response instance,
        // else set the return value as content
        if($response === null)
            $response = $this->container["response"];
        else if (!$response instanceof Response){
            if($response instanceof View)
                $response = $this->container["response"]->setContent($response->render());
            else
                $response = $this->container["response"]->setContent($response);
        }


        return $response;
    }

    /**
     * @param Container $container
     * @return Route
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param Router $router
     * @return Route
     */
    public function setRouter(Router $router): static
    {
        $this->router = $router;
        return $this;
    }
}