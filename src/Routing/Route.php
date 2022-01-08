<?php

namespace Curfle\Routing;

use Curfle\Agreements\Container\Container;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\Http\MiddlewareNotFoundException;
use Curfle\Support\Exceptions\Routing\MissingControllerInformationException;
use Curfle\Support\Str;
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
    private array $action;

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
     * @throws MissingControllerInformationException
     */
    public function __construct(array|string $methods, string $uri, callable|array|null $action)
    {
        $this->setUri($uri)
            ->setMethods(is_array($methods) ? $methods : [$methods])
            ->setAction($action);
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
     * Returns all matches of an uri and its parameters against this route.
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
            // get name of parameter
            $name = Str::substring($match[0], 1, -1);

            // get index in matches
            $index = $i + 1;
            for ($j = 0; $j < $i; $j++) {
                $index += substr_count($this->where[array_keys($this->where)[$j]], "(");
            }

            // set parameter value
            $value = $matches[$index][0][0];
            if($value !== "")
                $parameters[$name] = $value;
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
            $uri = Str::replace($uri, "{{$parameter}}", "($regex)");
        }

        $uri = Str::replace($uri, "/", "\/");

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
        // check for controller and resolve via the controller method if a controller is used
        if ($this->action["useController"]) {
            $controller = $this->container->make($this->action["controller"]);
            $response = $controller->callAction($this->action["method"], $request->inputs());
        } else {
            // action contains a callable
            $response = $this->container->call($this->action["callable"]);
        }

        // if response is null, default it to the apps' singleton response instance,
        // else set the return value as content
        if ($response === null)
            $response = $this->container["response"];
        else if (!$response instanceof Response) {
            if ($response instanceof View)
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

    /**
     * Sets the routes' uri.
     *
     * @param string $uri
     * @return Route
     */
    public function setUri(string $uri): static
    {
        // remove the trailing slash from url if it is not the web root.
        // trailing slashs should only be used to inditace a presence of
        // a directory, which is not the case with routes.
        $this->uri = $uri === "/" ? "/" : rtrim($uri, "/");
        return $this;
    }

    /**
     * Sets the routes' methods.
     *
     * @param array $methods
     * @return Route
     */
    public function setMethods(array $methods): static
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * Sets the routes' actions.
     *
     * @param mixed $action
     * @return Route
     * @throws MissingControllerInformationException
     */
    public function setAction(callable|array|null $action): static
    {
        $this->action = RouteAction::parse($this->uri, $action);
        return $this;
    }
}