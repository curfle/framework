<?php

namespace Curfle\Essence\Bootstrap;

use Curfle\Agreements\Essence\Bootstrap\BootstrapInterface;
use Curfle\Essence\Application;
use Curfle\Support\Facades\Facade;

class RegisterFacade implements BootstrapInterface
{

    function bootstrap(Application $app): void
    {
        Facade::setFacadeApplication($app);
    }
}