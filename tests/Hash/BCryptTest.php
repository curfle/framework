<?php

namespace Curfle\Tests\Hash;

use Curfle\Config\Repository;
use Curfle\Essence\Application;
use Curfle\Hash\Algorithm\BCrypt;
use Curfle\Hash\Algorithm\MD5;
use Curfle\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class BCryptTest extends TestCase
{

    protected function setUp(): void
    {
        // fake application
        $app = new Application();
        $app->singleton("config", function() {
            return new Repository();
        });
        Facade::setFacadeApplication($app);
    }

    /**
     * test ::hash() and ::verify()
     */
    public function testHashVerifyPositive()
    {
        $string = "MY_PASSWORD";
        $hash = BCrypt::hash($string);
        $this->assertTrue(
            BCrypt::verify($string, $hash)
        );
    }

    /**
     * test ::hash() and ::verify()
     */
    public function testHashVerifyNegative()
    {
        $string = "MY_PASSWORD";
        $hash = BCrypt::hash($string);
        $this->assertFalse(
            BCrypt::verify("OTHER_PASSWORD", $hash)
        );
    }

    /**
     * test ::needsRehash()
     */
    public function testNeedsRehashPositive()
    {
        $string = "MY_PASSWORD";
        $hash = BCrypt::hash($string);
        $this->assertFalse(
            BCrypt::needsRehash($hash)
        );
    }

    /**
     * test ::needsRehash()
     */
    public function testNeedsRehashNegative()
    {
        $string = "MY_PASSWORD";
        $hash = BCrypt::hash($string);
        $this->assertTrue(
            BCrypt::needsRehash($hash, ["cost" => 10])
        );
    }
}