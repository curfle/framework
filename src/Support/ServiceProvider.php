<?php

namespace Curfle\Support;

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
     * ServiceProvider constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    function register(): void
    {
    }

    /**
     * Boot the service provider. Is called after all service provider have ben registered.
     *
     * @return void
     */
    function boot(): void
    {
    }
}