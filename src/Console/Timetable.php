<?php

namespace Curfle\Console;

use Curfle\Chronos\Chronos;
use Curfle\Support\Arr;
use Curfle\Support\Str;

class Timetable
{
    /**
     * The day constants.
     */
    public const SUNDAY = 0;
    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY = 4;
    public const FRIDAY = 5;
    public const SATURDAY = 6;

    /**
     * The timetable's active minutes.
     *
     * @var array
     */
    private array $minutes = [];

    /**
     * The timetable's active hours.
     *
     * @var array
     */
    private array $hours = [];

    /**
     * The timetable's active days.
     *
     * @var array
     */
    private array $days = [];

    /**
     * The timetable's active months.
     *
     * @var array
     */
    private array $months = [];

    /**
     * The timetable's active wekdays.
     *
     * @var array
     */
    private array $weekdays = [];

    /**
     * Returns if the timetable configuration matches the given timestamp.
     *
     * @param Chronos $timestamp
     * @return bool
     */
    public function isDue(Chronos $timestamp): bool
    {
        // check for same minute
        if(!Arr::empty($this->minutes)
            && !Arr::in($this->minutes, $timestamp->minute()))
            return false;

        // check for same hour
        if(!Arr::empty($this->hours)
            && !Arr::in($this->hours, $timestamp->hour()))
            return false;

        // check for same day
        if(!Arr::empty($this->days)
            && !Arr::in($this->days, $timestamp->day()))
            return false;

        // check for same weekday
        if(!Arr::empty($this->weekdays)
            && !Arr::in($this->weekdays, $timestamp->day()))
            return false;

        return true;
    }

    /**
     * @return $this
     */
    public function everyMinute(): static
    {
        $this->minutes = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function everyTwoMinutes(): static
    {
        $this->minutes = range(0, 59, 2);
        return $this;
    }

    /**
     * @return $this
     */
    public function everyThreeMinutes(): static
    {
        $this->minutes = range(0, 59, 3);
        return $this;
    }

    /**
     * @return $this
     */
    public function everyFourMinutes(): static
    {
        $this->minutes = range(0, 59, 4);
        return $this;
    }

    /**
     * @return $this
     */
    public function everyFiveMinutes(): static
    {
        $this->minutes = range(0, 59, 5);
        return $this;
    }

    /**
     * @return $this
     */
    public function everyTenMinutes(): static
    {
        $this->minutes = range(0, 59, 10);
        $this->hours = $this->days = $this->months = $this->weekdays = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function everyFifteenMinutes(): static
    {
        $this->minutes = range(0, 59, 15);
        $this->hours = $this->days = $this->months = $this->weekdays = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function everyThirtyMinutes(): static
    {
        $this->minutes = range(0, 59, 10);
        $this->hours = $this->days = $this->months = $this->weekdays = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function hourly(): static
    {
        $this->minutes = [0];
        $this->hours = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function hourlyAt(int $minutes): static
    {
        $this->minutes = [$minutes];
        $this->hours = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function everyTwoHours(): static
    {
        $this->minutes = [0];
        $this->hours = range(0, 23, 2);
        return $this;
    }

    /**
     * @return $this
     */
    public function everyThreeHours(): static
    {
        $this->minutes = [0];
        $this->hours = range(0, 23, 3);
        return $this;
    }

    /**
     * @return $this
     */
    public function everyFourHours(): static
    {
        $this->minutes = [0];
        $this->hours = range(0, 23, 4);
        return $this;
    }

    /**
     * @return $this
     */
    public function everySixHours(): static
    {
        $this->minutes = [0];
        $this->hours = range(0, 23, 6);
        return $this;
    }

    /**
     * @return $this
     */
    public function daily(): static
    {
        $this->minutes = $this->hours = [0];
        $this->days = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function dailyAt(string $time): static
    {
        [$hour, $minutes] = Str::split($time, ":");
        $this->minutes = [(int)$minutes];
        $this->hours = [(int)$hour];
        $this->days = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function weekly(): static
    {
        $this->minutes = $this->hours = $this->days = [0];
        $this->weekdays = [self::MONDAY];
        return $this;
    }

    /**
     * @return $this
     */
    public function weeklyOn(int $day, string $time): static
    {
        [$hour, $minutes] = Str::split($time, ":");
        $this->minutes = [(int)$minutes];
        $this->hours = [(int)$hour];
        $this->days = [];
        $this->weekdays = [$day];
        return $this;
    }

    /**
     * @return $this
     */
    public function monthly(): static
    {
        $this->minutes = $this->hours = $this->days = [0];
        $this->weekdays = [];
        $this->days = [1];
        return $this;
    }

    /**
     * @return $this
     */
    public function monthlyOn(int $day, string $time): static
    {
        [$hour, $minutes] = Str::split($time, ":");
        $this->minutes = [(int)$minutes];
        $this->hours = [(int)$hour];
        $this->days = [$day];
        $this->weekdays = [];
        return $this;
    }

}
