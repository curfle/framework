<?php

namespace Curfle\Tests\Hash;

use Curfle\Hash\Algorithm\MD5;
use Curfle\Hash\Algorithm\SHA1;
use Curfle\Hash\Algorithm\SHA256;
use Curfle\Hash\Algorithm\SHA512;
use PHPUnit\Framework\TestCase;

class SHA512Test extends TestCase
{
    /**
     * test ::hash()
     */
    public function testHash()
    {
        $this->assertEquals(
            "9ea142c91fa2f69a373555598031a7790fcf92827249e09135191838525dffe298856fff23288d4863822a628d831492e42783cdea60c0a67ca973d21f0504d5",
            SHA512::hash("HASHED CONTENT")
        );
    }
}