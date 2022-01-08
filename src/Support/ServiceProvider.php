<?php

namespace Curfle\Support;

use Closure;
use Curfle\Essence\Application;

abstract class ServiceProvider
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Provided bindings to be bound by the app
     *
     * @var array
     */
    public array $bindings = [];

    /**
     * Provided singletons to be bound by the app
     *
     * @var array
     */
    public array $singletons = [];

    /**
     * All the registered booting callbacks.
     *
     * @var array
     */
    protected array $bootingCallbacks = [];

    /**
     * All the registered booted callbacks.
     *
     * @var array
     */
    protected array $bootedCallbacks = [];



    /**
     * ServiceProvider constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register a booting callback to be run before the "boot" method is called.
     *
     * @param Closure $callback
     * @return void
     */
    public function booting(Closure $callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a booted callback to be run after the "boot" method is called.
     *
     * @param Closure $callback
     * @return void
     */
    public function booted(Closure $callback)
    {
        $this->bootedCallbacks[] = $callback;
    }

    /**
     * Call the registered booting callbacks.
     *
     * @return void
     */
    public function callBootingCallbacks()
    {
        foreach ($this->bootingCallbacks as $callback) {
            $this->app->call($callback);
        }
    }

    /**
     * Call the registered booted callbacks.
     *
     * @return void
     */
    public function callBootedCallbacks()
    {
        foreach ($this->bootedCallbacks as $callback) {
            $this->app->call($callback);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Boot the service provider. Is called after all service providers have been registered.
     *
     * @return void
     */
    public function boot()
    {
    }
}