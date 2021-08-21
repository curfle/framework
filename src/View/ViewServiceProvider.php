<?php

namespace Curfle\View;

use Curfle\Essence\Application;
use Curfle\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function boot(){
        ViewName::setApplicationInstance($this->app);
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->app->singleton(ViewFactory::class, function (Application $app) {
            return new ViewFactory($app);
        });
    }
}