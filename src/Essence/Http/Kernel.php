<?php

namespace Curfle\Essence\Http;

use Closure;
use Curfle\Agreements\Http\Kernel as KernelAgreement;
use Curfle\Essence\Application;
use Curfle\Essence\Bootstrap\BootProviders;
use Curfle\Essence\Bootstrap\LoadConfiguration;
use Curfle\Essence\Bootstrap\LoadEnvironmentVariables;
use Curfle\Essence\Bootstrap\RegisterFacade;
use Curfle\Essence\Bootstrap\RegisterProviders;
use Curfle\Essence\Exceptions\ExceptionHandler;
use Curfle\Routing\Router;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Support\Exceptions\BindingResolutionException;
use Curfle\Support\Exceptions\CircularDependencyException;
use Curfle\Support\Exceptions\InvalidArgumentException;
use Curfle\Support\Exceptions\NotFoundHttpException;
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
        BootProviders::class
    ];

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
     * Send the given request through the (middleware and) router.
     *
     * @param Request $request
     * @return Response
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     * @throws NotFoundHttpException
     */
    protected function sendRequestThroughRouter(Request $request): Response
    {
        $this->app->instance('request', $request);

        $this->bootstrap();

        return $this->router->resolve($request);
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
     */
    protected function reportException(Throwable $e)
    {
        $this->app[ExceptionHandler::class]->report($e);
    }

    /**
     * Render the exception to a response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     */
    protected function renderException(Request $request, Throwable $e): Response
    {
        return $this->app[ExceptionHandler::class]->render($request, $e);
    }
}