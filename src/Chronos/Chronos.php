<?php

namespace Curfle\Chronos;

use DateTimeZone;
use Exception;

class Chronos extends \DateTime
{
    /**
     * Holds the timezone of the date.
     *
     * @var DateTimeZone|null
     */
    static ?DateTimeZone $timezone = null;

    /**
     * Set the new default timezone.
     *
     * @param string $timezone
     * @return void
     */
    public static function useTimezone(string $timezone)
    {
        self::$timezone = new DateTimeZone($timezone);
    }

    /**
     * Returns the current timestamp as Chronos object.
     *
     * @param string|null $timezone
     * @return Chronos
     * @throws Exception
     */
    public static function now(?string $timezone = null): Chronos
    {
        return static::parse("now", $timezone);
    }

    /**
     * Returns the current timestamp as Chronos object.
     *
     * @param string $time
     * @param string|null $timezone
     * @return Chronos
     * @throws Exception
     */
    public static function parse(string $time, ?string $timezone = null): Chronos
    {
        // parse timezone
        if ($timezone !== null)
            $timezone = new DateTimeZone($timezone);

        return new Chronos($time, $timezone ?? self::$timezone);
    }


    /**
     * Returns the year.
     *
     * @return int
     */
    public function year(): int
    {
        return (int)$this->format("Y");
    }

    /**
     * Returns the month.
     *
     * @return int
     */
    public function month(): int
    {
        return (int)$this->format("m");
    }

    /**
     * Returns the day.
     *
     * @return int
     */
    public function day(): int
    {
        return (int)$this->format("d");
    }

    /**
     * Returns the hour.
     *
     * @return int
     */
    public function hour(): int
    {
        return (int)$this->format("H");
    }

    /**
     * Returns the minute.
     *
     * @return int
     */
    public function minute(): int
    {
        return (int)$this->format("i");
    }

    /**
     * Returns the second.
     *
     * @return int
     */
    public function second(): int
    {
        return (int)$this->format("s");
    }

    /**
     * Returns the second.
     *
     * @return int
     */
    public function microSecond(): int
    {
        return (int)$this->format("u");
    }

    /**
     * Returns the second.
     *
     * @return int
     */
    public function milliSecond(): int
    {
        return (int)$this->format("v");
    }

    /**
     * Returns the weekDay.
     *
     * @return int
     */
    public function weekDay(): int
    {
        return (int)$this->format("w");
    }

    /**
     * Returns the year.
     *
     * @return int
     */
    public function yearDay(): int
    {
        return (int)$this->format("z");
    }

    /**
     * Returns the week.
     *
     * @return int
     */
    public function week(): int
    {
        return (int)$this->format("W");
    }

    /**
     * Returns wether the year is a leap year.
     *
     * @return bool
     */
    public function isLeapYear(): bool
    {
        return (bool)$this->format("Y");
    }

    /**
     * Returns the formatted timestamp.
     *
     * @param string|null $format
     * @return string
     */
    public function get(?string $format = null): string
    {
        return $this->format($format ?? "Y-m-d H:i:s");
    }
}