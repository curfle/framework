<?php

namespace Curfle\Tests\Hash;

use Curfle\Hash\Algorithm\Argon2i;
use Curfle\Hash\Algorithm\BCrypt;
use Curfle\Hash\Algorithm\MD5;
use PHPUnit\Framework\TestCase;

class Argon2iTest extends TestCase
{
    protected function setUp(): void
    {
        // skip test if on m1 mac with no argon2i support
        if(exec("sysctl -n machdep.cpu.brand_string") === "Apple M1")
            $this->markTestSkipped("System uses Apple M1 chip");
    }

    /**
     * test ::hash() and ::verify()
     */
    public function testHashVerifyPositive()
    {
        $string = "MY_PASSWORD";
        $hash = Argon2i::hash($string);
        $this->assertTrue(
            Argon2i::verify($string, $hash)
        );
    }

    /**
     * test ::hash() and ::verify()
     */
    public function testHashVerifyNegative()
    {
        $string = "MY_PASSWORD";
        $hash = Argon2i::hash($string);
        $this->assertFalse(
            Argon2i::verify("OTHER_PASSWORD", $hash)
        );
    }

    /**
     * test ::needsRehash()
     */
    public function testNeedsRehashPositive()
    {
        $string = "MY_PASSWORD";
        $hash = Argon2i::hash($string);
        $this->assertFalse(
            Argon2i::needsRehash($hash)
        );
    }

    /**
     * test ::needsRehash()
     */
    public function testNeedsRehashNegative()
    {
        $string = "MY_PASSWORD";
        $hash = Argon2i::hash($string);
        $this->assertTrue(
            Argon2i::needsRehash($hash, ["memory_cost" => 65000])
        );
    }
}