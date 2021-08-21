<?php

namespace Curfle\Tests\Helpers;

use Curfle\Essence\Application;
use Curfle\Support\Env\Env;
use Curfle\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application();
        Facade::setFacadeApplication($app);
        Env::set("APP_URL", "https://curfle.org");
    }

    /**
     * test asset()
     */
    public function testAsset()
    {
        $this->assertEquals(
            "/assets/css/main.css",
            asset("css/main.css")
        );
        $this->assertEquals(
            "/assets/css/main.css",
            asset("/css/main.css")
        );
    }

    /**
     * test url()
     */
    public function testUrl()
    {
        $this->assertEquals(
            "https://curfle.org/user/profile",
            url("user/profile")
        );
        $this->assertEquals(
            "https://curfle.org/user/profile",
            url("/user/profile")
        );
        $this->assertEquals(
            "https://curfle.org/user/profile/",
            url("user/profile/")
        );
        $this->assertEquals(
            "https://curfle.org/user/profile/",
            url("/user/profile/")
        );
    }
}