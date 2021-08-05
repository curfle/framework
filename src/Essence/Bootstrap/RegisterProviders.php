<?php

namespace Curfle\Essence\Bootstrap;

use Curfle\Agreements\Essence\Bootstrap\BootstrapInterface;
use Curfle\Essence\Application;

class RegisterProviders implements BootstrapInterface
{

    /**
     * @inheritDoc
     */
    function bootstrap(Application $app)
    {
        $app->registerConfiguredProviders();
    }
}