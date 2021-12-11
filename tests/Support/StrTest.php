<?php

namespace Curfle\Tests\Supprt;

use Curfle\Routing\Route;
use Curfle\Support\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    /**
     * Tests the ::contains function.
     */
    public function testContains()
    {
        $this->assertTrue(Str::contains("Hello world!", "world"));
        $this->assertFalse(Str::contains("Hello world!", "planet"));
    }

    /**
     * Tests the ::startsWith function.
     */
    public function testStartsWith()
    {
        $this->assertTrue(Str::startsWith("Hello world!", "Hello"));
        $this->assertFalse(Str::startsWith("Hello world!", "Hey"));
    }

    /**
     * Tests the ::until function.
     */
    public function testUntil()
    {
        $this->assertSame("Hello", Str::until("Hello world!", " "));
    }

    /**
     * Tests the ::from function.
     */
    public function testFrom()
    {
        $this->assertSame("world!", Str::from("Hello world!", " "));
    }

    /**
     * Tests the ::substring function.
     */
    public function testSubstring()
    {
        $this->assertSame("Hello", Str::substring("Hello world!", 0, 5));
    }

    /**
     * Tests the ::split function.
     */
    public function testSplit()
    {
        $this->assertEquals(["Hello", "world!"], Str::split("Hello world!"));
    }

    /**
     * Tests the ::length function.
     */
    public function testLength()
    {
        $this->assertSame(12, Str::length("Hello world!"));
    }

    /**
     * Tests the ::replace function.
     */
    public function testReplace()
    {
        $this->assertSame("Hello planet!", Str::replace("Hello world!", "world", "planet"));
    }

    /**
     * Tests the ::trim function.
     */
    public function testTrim()
    {
        $this->assertSame("Hello world!", Str::trim(" Hello world! "));
        $this->assertSame("Hello world", Str::trim(" Hello world! ", " !"));
    }

    /**
     * Tests the ::find function.
     */
    public function testFind()
    {
        $this->assertSame(6, Str::find("Hello world!", "world"));
    }

    /**
     * Tests the ::lower function.
     */
    public function testLower()
    {
        $this->assertSame("hello world!", Str::lower("Hello world!"));
    }

    /**
     * Tests the ::upper function.
     */
    public function testUpper()
    {
        $this->assertSame("HELLO WORLD!", Str::upper("Hello world!"));
    }
}