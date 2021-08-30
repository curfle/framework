<?php

namespace Curfle\Tests\Auth\JWT;


use Curfle\Auth\JWT\JWT;
use Curfle\Chronos\Chronos;
use Curfle\Support\Env\Env;
use Curfle\Support\Exceptions\Misc\SecretNotPresentException;
use Exception;
use PHPUnit\Framework\TestCase;

class ChronosTest extends TestCase
{

    /**
     * Tests the ::parse() function.
     * @throws Exception
     */
    public function testParse()
    {
        $chronos = Chronos::parse("2021-08-07 20:08:07", "EUROPE/Berlin");

        $this->assertSame(2021, $chronos->year());
        $this->assertSame(8, $chronos->month());
        $this->assertSame(7, $chronos->day());
        $this->assertSame(6, $chronos->weekDay());
        $this->assertSame(218, $chronos->yearDay());
        $this->assertSame(31, $chronos->week());
        $this->assertSame(20, $chronos->hour());
        $this->assertSame(8, $chronos->minute());
        $this->assertSame(7, $chronos->second());
        $this->assertSame(0, $chronos->milliSecond());
        $this->assertSame(0, $chronos->microSecond());
    }

    /**
     * Tests the ::parse() function.
     * @throws Exception
     */
    public function testParseRelative()
    {
        date_default_timezone_set("EUROPE/Berlin");
        $chronos = Chronos::parse("-1 minutes", "EUROPE/Berlin");

        $this->assertSame((int)date("Y"), $chronos->year());
        $this->assertSame((int)date("m"), $chronos->month());
        $this->assertSame((int)date("d"), $chronos->day());
        $this->assertSame((int)date("w"), $chronos->weekDay());
        $this->assertSame((int)date("z"), $chronos->yearDay());
        $this->assertSame((int)date("W"), $chronos->week());
        $this->assertSame((int)date("H"), $chronos->hour());
        $this->assertSame(((int)date("i")) - 1, $chronos->minute());
        $this->assertSame((int)date("s"), $chronos->second());
    }

    /**
     * Tests the ::parse() function.
     * @throws Exception
     */
    public function testNow()
    {
        Chronos::useTimezone("EUROPE/Berlin");
        date_default_timezone_set("EUROPE/Berlin");
        $chronos = Chronos::now();

        $this->assertSame((int)date("Y"), $chronos->year());
        $this->assertSame((int)date("m"), $chronos->month());
        $this->assertSame((int)date("d"), $chronos->day());
        $this->assertSame((int)date("w"), $chronos->weekDay());
        $this->assertSame((int)date("z"), $chronos->yearDay());
        $this->assertSame((int)date("W"), $chronos->week());
        $this->assertSame((int)date("H"), $chronos->hour());
        $this->assertSame((int)date("i"), $chronos->minute());
        $this->assertSame((int)date("s"), $chronos->second());
    }

    /**
     * Tests the ::get() function.
     * @throws Exception
     */
    public function testGet()
    {
        Chronos::useTimezone("EUROPE/Berlin");
        $chronos = Chronos::parse("2021-08-07 20:08:07");

        $this->assertSame("2021-08-07 20:08:07", $chronos->get());
        $this->assertSame("2021-08-07", $chronos->get("Y-m-d"));
    }
}