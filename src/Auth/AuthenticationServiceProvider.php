<?php

namespace Curfle\Auth;

use Curfle\Essence\Application;
use Curfle\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAuthenticationManager();
    }

    /**
     * Register the authentication manager.
     *
     * @return void
     */
    protected function registerAuthenticationManager()
    {
        $this->app->singleton("auth", function (Application $app) {
            return new AuthenticationManager($app);
        });
    }
}