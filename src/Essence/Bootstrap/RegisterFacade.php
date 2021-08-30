<?php

namespace Curfle\Essence\Bootstrap;

use Curfle\Agreements\Essence\Bootstrap\BootstrapInterface;
use Curfle\Essence\Application;
use Curfle\Support\Facades\Facade;

class RegisterFacade implements BootstrapInterface
{

    /**
     * @inheritDoc
     */
    function bootstrap(Application $app)
    {
        Facade::setFacadeApplication($app);
    }
}