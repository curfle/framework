<?php

namespace Curfle\Tests\Hash;

use Curfle\Hash\Algorithm\MD5;
use Curfle\Hash\Algorithm\SHA1;
use PHPUnit\Framework\TestCase;

class SHA1Test extends TestCase
{
    /**
     * test ::hash()
     */
    public function testHash()
    {
        $this->assertEquals(
            "8c7522c53fff7c4b3cb82ec911a2840f86c3009c",
            SHA1::hash("HASHED CONTENT")
        );
    }
}