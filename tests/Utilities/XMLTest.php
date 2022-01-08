<?php

namespace Curfle\Tests\Utilities;

use Curfle\Utilities\XML;
use PHPUnit\Framework\TestCase;

class XMLTest extends TestCase
{
    /**
     * Tests the ::from() function.
     */
    public function testFrom()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<data><hello>world</hello><this><is>a</is><nested>xml</nested><nested>array</nested></this></data>
';
        $data = ["hello" => "world", "this" => ["is" => "a", "nested" => ["xml", "array"]]];

        self::assertSame(
            $xml,
            XML::from($data)
        );
    }

    /**
     * Tests the ::parse() function.
     */
    public function testParse()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><data><hello>world</hello><this><is>a</is><nested>xml</nested><nested>array</nested></this></data>';

        self::assertEquals(
            XML::parse($xml)->hello,
            "world"
        );
    }
}