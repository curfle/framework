<?php

namespace Curfle\Essence\Support\Providers;

use Closure;
use Curfle\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    /**
     * The callback that should be used to load the application's routes.
     *
     * @var Closure|null
     */
    protected ?Closure $loadRoutesUsing = null;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->booted(function () {
            $this->loadRoutes();
        });
    }

    /**
     * Register the callback that will be used to load the application's routes.
     *
     * @param Closure $routesCallback
     * @return $this
     */
    protected function routes(Closure $routesCallback): static
    {
        $this->loadRoutesUsing = $routesCallback;
        return $this;
    }

    /**
     * Load the application routes.
     *
     * @return void
     */
    public function loadRoutes()
    {
        if (!is_null($this->loadRoutesUsing))
            $this->app->call($this->loadRoutesUsing);
    }

}