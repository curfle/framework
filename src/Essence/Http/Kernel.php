<?php

namespace Curfle\Essence\Http;

use Curfle\Agreements\Http\Kernel as KernelAgreement;
use Curfle\Essence\Application;
use Curfle\Essence\Bootstrap\BootProviders;
use Curfle\Essence\Bootstrap\LoadConfiguration;
use Curfle\Essence\Bootstrap\LoadEnvironmentVariables;
use Curfle\Essence\Bootstrap\ReformatRequest;
use Curfle\Essence\Bootstrap\RegisterFacade;
use Curfle\Essence\Bootstrap\RegisterProviders;
use Curfle\Essence\Exceptions\ExceptionHandler;
use Curfle\Routing\Router;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\Http\Dispatchable\HttpNotFoundException;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use ReflectionException;
use Throwable;

class Kernel implements KernelAgreement
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * The router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * The bootstrap classes for the application.
     *
     * @var string[]
     */
    protected array $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfiguration::class,
        RegisterFacade::class,
        RegisterProviders::class,
        BootProviders::class,
        ReformatRequest::class
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
     * Create a new HTTP kernel instance.
     *
     * @param Application $app
     * @param Router $router
     * @return void
     */
    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;

        $this->syncMiddlewareToRouter();
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request): Response
    {
        try {
            $response = $this->sendRequestThroughRouter($request);
        } catch (Throwable $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function terminate()
    {
        $this->app->terminate();
    }

    /**
     * Send the given request through the (middleware and) router.
     *
     * @param Request $request
     * @return Response
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     * @throws HttpNotFoundException
     */
    protected function sendRequestThroughRouter(Request $request): Response
    {
        $this->app->instance('request', $request);
        $this->app->instance('response', new Response());

        $this->bootstrap();

        return $this->router->resolve($request);
    }

    /**
     * Informs the router about the middlewares bound to the kernel.
     *
     * @return void
     */
    private function syncMiddlewareToRouter()
    {
        foreach ($this->middleware as $middleware) {
            $this->router->addGlobalMiddleware($middleware);
        }

        foreach ($this->middlewareGroups as $groupName => $middlewareGroup) {
            $this->router->groupMiddleware($groupName, $middlewareGroup);
        }

        foreach ($this->routeMiddleware as $alias => $middleware) {
            $this->router->aliasMiddleware($alias, $middleware);
        }
    }

    /**
     * @inheritDoc
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     */
    public function bootstrap()
    {
        if (!$this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }
    }

    /**
     * @inheritDoc
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers(): array
    {
        return $this->bootstrappers;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param Throwable $e
     * @return void
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws ReflectionException
     */
    protected function reportException(Throwable $e)
    {
        $this->app->resolve(ExceptionHandler::class)->report($e);
    }

    /**
     * Render the exception to a response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws ReflectionException
     */
    protected function renderException(Request $request, Throwable $e): Response
    {
        return $this->app->resolve(ExceptionHandler::class)->render($request, $e);
    }
}