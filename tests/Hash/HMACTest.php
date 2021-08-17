<?php

namespace Curfle\Tests\Hash;

use Curfle\Hash\Algorithm\HMAC;
use Curfle\Hash\Algorithm\MD5;
use PHPUnit\Framework\TestCase;

class HMACTest extends TestCase
{
    /**
     * test ::hash()
     */
    public function testHash()
    {
        $this->assertEquals(
            "6c5435690a6eee60d2d5563b53a83a0f8d63c88fd6d3e28c4b3a4b1357b9c46f",
            HMAC::hash("HASHED CONTENT", "SUPER SECRET", binary: false)
        );
    }
}