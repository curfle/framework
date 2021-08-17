<?php

namespace Curfle\Tests\Hash;

use Curfle\Hash\Algorithm\BCrypt;
use Curfle\Hash\Algorithm\MD5;
use PHPUnit\Framework\TestCase;

class BCryptTest extends TestCase
{
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