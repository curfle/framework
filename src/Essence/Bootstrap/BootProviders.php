<?php

namespace Curfle\Essence\Bootstrap;

use Curfle\Agreements\Essence\Bootstrap\BootstrapInterface;
use Curfle\Essence\Application;

class BootProviders implements BootstrapInterface
{
    /**
     * Bootstrap the given application.
     *
     * @param  Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->boot();
    }
}