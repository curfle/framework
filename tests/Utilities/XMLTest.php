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
<data><hello>world</hello><this><is>a</is><nested><item0>array</item0></nested></this></data>
';
        $data = ["hello" => "world", "this" => ["is" => "a", "nested" => ["array"]]];

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
        $xml = '<?xml version="1.0" encoding="UTF-8"?><data><hello>world</hello><this><is>a</is><nested><item0>array</item0></nested></this></data>';
        $data = ["hello" => "world", "this" => ["is" => "a", "nested" => ["item0" => "array"]]];

        self::assertEquals(
            $data,
            XML::parse($xml)
        );
    }
}