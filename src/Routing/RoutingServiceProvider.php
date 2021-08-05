<?php

namespace Curfle\Routing;

use Curfle\Support\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouter();
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    private function registerRouter()
    {
        $this->app->singleton('router', function($app) {
            return new Router($app);
        });
    }
}