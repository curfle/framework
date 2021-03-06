<?php

namespace Curfle\Essence\Bootstrap;

use Curfle\Agreements\Essence\Bootstrap\BootstrapInterface;
use Curfle\Essence\Application;
use Curfle\Support\Env\Env;
use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;

class LoadEnvironmentVariables implements BootstrapInterface
{

    /**
     * @inheritDoc
     */
    function bootstrap(Application $app)
    {
        Env::getRepository()
            ->setPath($app->environmentPath() . DIRECTORY_SEPARATOR . $app->environmentFile())
            ->load();
    }
}