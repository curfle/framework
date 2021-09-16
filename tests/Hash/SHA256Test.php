<?php

namespace Curfle\Tests\Hash;

use Curfle\Hash\Algorithm\MD5;
use Curfle\Hash\Algorithm\SHA1;
use Curfle\Hash\Algorithm\SHA256;
use PHPUnit\Framework\TestCase;

class SHA256Test extends TestCase
{
    /**
     * test ::hash()
     */
    public function testHash()
    {
        $this->assertEquals(
            "f6728c0ec703d58072cfd6cb1a3ca2a9fec006b556df752c14dd02de470fa6d9",
            SHA256::hash("HASHED CONTENT")
        );
    }
}