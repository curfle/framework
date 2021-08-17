<?php

namespace Curfle\Tests\Hash;

use Curfle\Hash\Algorithm\MD5;
use PHPUnit\Framework\TestCase;

class MD5Test extends TestCase
{
    /**
     * test ::hash()
     */
    public function testHash()
    {
        $this->assertEquals(
            "191ec950ba51904a54509a9ff9e2998a",
            MD5::hash("HASHED CONTENT")
        );
    }
}