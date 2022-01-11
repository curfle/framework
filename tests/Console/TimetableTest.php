<?php

namespace Curfle\Tests\Auth\JWT;


use Curfle\Chronos\Chronos;
use Curfle\Console\Timetable;
use Exception;
use PHPUnit\Framework\TestCase;

class TimetableTest extends TestCase
{

    private Chronos $timestampOne;
    private Chronos $timestampTwo;
    private Chronos $timestampThree;
    private Chronos $timestampFour;
    private Chronos $timestampFive;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->timestampOne = Chronos::parse("2006-04-15 12:15:17");
        $this->timestampTwo = Chronos::parse("2006-04-01 09:57:59");
        $this->timestampThree = Chronos::parse("2006-04-15 00:00:16");
        $this->timestampFour = Chronos::parse("2006-04-17 16:30:12");
        $this->timestampFive = Chronos::parse("2006-04-17 18:40:08");
    }

    public function testEveryMinute()
    {
        $timetable = (new Timetable())->everyMinute();
        $this->assertTrue($timetable->isDue($this->timestampOne));
        $this->assertTrue($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertTrue($timetable->isDue($this->timestampFour));
        $this->assertTrue($timetable->isDue($this->timestampFive));
    }

    public function testEveryTwoMinutes()
    {
        $timetable = (new Timetable())->everyTwoMinutes();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertTrue($timetable->isDue($this->timestampFour));
        $this->assertTrue($timetable->isDue($this->timestampFive));
    }

    public function testEveryThreeMinutes()
    {
        $timetable = (new Timetable())->everyThreeMinutes();
        $this->assertTrue($timetable->isDue($this->timestampOne));
        $this->assertTrue($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertTrue($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testEveryFourMinutes()
    {
        $timetable = (new Timetable())->everyFourMinutes();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertTrue($timetable->isDue($this->timestampFive));
    }

    public function testEveryFiveMinutes()
    {
        $timetable = (new Timetable())->everyFiveMinutes();
        $this->assertTrue($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertTrue($timetable->isDue($this->timestampFour));
        $this->assertTrue($timetable->isDue($this->timestampFive));
    }

    public function testEveryTenMinutes()
    {
        $timetable = (new Timetable())->everyTenMinutes();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertTrue($timetable->isDue($this->timestampFour));
        $this->assertTrue($timetable->isDue($this->timestampFive));
    }

    public function testEveryFifeteenMinutes()
    {
        $timetable = (new Timetable())->everyFifteenMinutes();
        $this->assertTrue($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertTrue($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testEveryThirtyMinutes()
    {
        $timetable = (new Timetable())->everyThirtyMinutes();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertTrue($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testHourly()
    {
        $timetable = (new Timetable())->hourly();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testHourlyAt()
    {
        $timetable = (new Timetable())->hourlyAt(57);
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertTrue($timetable->isDue($this->timestampTwo));
        $this->assertFalse($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testEveryTwoHours()
    {
        $timetable = (new Timetable())->everyTwoHours();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testEveryThreeHours()
    {
        $timetable = (new Timetable())->everyThreeHours();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testEveryFourHours()
    {
        $timetable = (new Timetable())->everyFourHours();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testEverySixHours()
    {
        $timetable = (new Timetable())->everySixHours();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testDaily()
    {
        $timetable = (new Timetable())->daily();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertTrue($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testDailyAt()
    {
        $timetable = (new Timetable())->dailyAt("12:15");
        $this->assertTrue($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertFalse($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testWeekly()
    {
        $timetable = (new Timetable())->weekly();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertFalse($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function weeklyOn()
    {
        $timetable = (new Timetable())->weeklyOn(Timetable::SATURDAY, "12:15");
        $this->assertTrue($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertFalse($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testMonthly()
    {
        $timetable = (new Timetable())->monthly();
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertFalse($timetable->isDue($this->timestampThree));
        $this->assertFalse($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }

    public function testMonthlyOn()
    {
        $timetable = (new Timetable())->monthlyOn(17, "16:30");
        $this->assertFalse($timetable->isDue($this->timestampOne));
        $this->assertFalse($timetable->isDue($this->timestampTwo));
        $this->assertFalse($timetable->isDue($this->timestampThree));
        $this->assertTrue($timetable->isDue($this->timestampFour));
        $this->assertFalse($timetable->isDue($this->timestampFive));
    }
}