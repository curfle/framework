<?php

namespace Curfle\Tests\Utilities;

use Curfle\Utilities\JSON;
use PHPUnit\Framework\TestCase;

class JSONTest extends TestCase
{
    /**
     * Tests the ::from() function.
     */
    public function testFrom()
    {
        $json = '{"hello":"world","this":{"is":"a","nested":["array"]}}';
        $data = ["hello" => "world", "this" => ["is" => "a", "nested" => ["array"]]];

        self::assertSame(
            $json,
            JSON::from($data)
        );
    }

    /**
     * Tests the ::parse() function.
     */
    public function testParse()
    {
        $json = '{"hello":"world","this":{"is":"a","nested":["array"]}}';
        $data = ["hello" => "world", "this" => ["is" => "a", "nested" => ["array"]]];

        self::assertEquals(
            $data,
            JSON::parse($json),
        );
    }
}