<?php

namespace Curfle\Tests\Resources\DummyClasses;

use Curfle\Essence\Application;

class ClassWithDependencies{
    private Application $app;

    public function __construct(Application ...$app)
    {
        $this->app = $app[0];
    }

    /**
     * @return Application
     */
    public function getApp(): Application
    {
        return $this->app;
    }
}