<?php

namespace Curfle\Agreements\Essence\Bootstrap;

use \Curfle\Essence\Application;

interface BootstrapInterface
{
    /**
     * Bootstraps the interfaced class.
     *
     * @param Application $app
     */
    function bootstrap(Application $app) : void;
}