<?php

namespace Curfle\Essence;

use Curfle\Container\Container;
use Curfle\Routing\RoutingServiceProvider;
use Curfle\Support\Exceptions\LogicException;
use Curfle\Support\ServiceProvider;

class Application extends Container
{
    const VERSION = "8.0.0";

    /**
     * The application's boot state.
     *
     * @var bool
     */
    private bool $booted = false;

    /**
     * The application's base path.
     *
     * @var string
     */
    private string $basePath;

    /**
     * The application's service providers.
     *
     * @var array
     */
    private array $serviceProviders = [];

    /**
     * Application constructor.
     *
     * @param string|null $basePath
     */
    public function __construct(string $basePath = null)
    {
        if ($basePath !== null)
            $this->setBasePath($basePath);

        // TODO
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();

        // TODO -> when to boot?
    }

    /**
     * Returns whether application has been booted or not.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Boot the application.
     *
     * @return void
     */
    public function boot(): void
    {
        // skip if is already booted
        if ($this->isBooted())
            return;

        // boot all service providers
        array_walk($this->serviceProviders, function (ServiceProvider $provider) {
            $this->bootProvider($provider);
        });

        $this->booted = true;
    }

    /**
     * Boots a service provider
     *
     * @param ServiceProvider $provider
     * @return void
     */
    private function bootProvider(ServiceProvider $provider): void
    {
        $provider->boot();
    }

    /**
     * Sets the application's base path.
     *
     * @param string $basePath
     */
    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Returns the application's base path.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        // make $this available in Container
        static::$instance = $this;

        // add app and Container instance
        $this->instance("app", $this);
        $this->instance(Container::class, $this);

        // TODO: singleton here filesystem - with basePath?
    }

    /**
     * Register all the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        // TODO: register Logger
        $this->register(RoutingServiceProvider::class);
    }

    /**
     * Registers a service provider in the application.
     *
     * @param ServiceProvider|string $provider
     * @param bool $force
     * @return bool|void
     */
    public function register(ServiceProvider|string $provider, bool $force = false)
    {
        // check if cached service provider can be used
        if ($registered = $this->getProvider($provider) == null && !$force)
            return $registered;

        // if provider is just a class name -> resolve for developer
        if (is_string($provider))
            $provider = $this->resolveProvider($provider);

        // register provider
        $provider->register();

        // bind provider's binings
        foreach ($provider->bindings as $id => $resolver) {
            $this->bind($id, $resolver);
        }

        // bind provider's singletons
        foreach ($provider->singletons as $id => $resolver) {
            $this->singleton($id, $resolver);
        }

        // add service provider to service provider's list
        $this->serviceProviders[] = $provider;
    }

    /**
     * Returns a service provider of the application.
     *
     * @param ServiceProvider|string $provider
     * @return ServiceProvider|null
     */
    public function getProvider(ServiceProvider|string $provider): ?ServiceProvider
    {
        if (!is_string($provider))
            $provider = get_class($provider);

        return $this->getProviders()[$provider] ?? null;
    }

    /**
     * Returns all registered service providers.
     *
     * @return array
     */
    public function getProviders(): array
    {
        return $this->serviceProviders;
    }

    /**
     * Creates an instance of a ServiceProvider
     * @param string $provider
     * @return ServiceProvider
     */
    private function resolveProvider(string $provider): ServiceProvider
    {
        return new $provider($this);
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     * @throws LogicException
     */
    protected function registerCoreContainerAliases() : void
    {
        foreach([
            "app" => [self::class, Container::class, Application::class],
        ] as $id => $aliases){
            foreach ($aliases as $alias) {
                $this->alias($id, $alias);
            }
        }
    }
}