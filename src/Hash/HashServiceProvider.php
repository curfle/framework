<?php

namespace Curfle\Hash;

use Curfle\Support\Facades\Hash;
use Curfle\Support\ServiceProvider;

class HashServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHashManager();
    }

    /**
     * Register the native filesystem implementation.
     *
     * @return void
     */
    protected function registerHashManager()
    {
        $this->app->singleton("hash", function () {
            return new HashManager();
        });
    }
}