<?php

namespace Curfle\Mail;

use Curfle\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMailManager();
    }

    /**
     * Register the native filesystem implementation.
     *
     * @return void
     */
    protected function registerMailManager()
    {
        $this->app->singleton("mail", function () {
            return new MailManager();
        });
    }
}