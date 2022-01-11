<?php

namespace Curfle\Console;

use Closure;
use Curfle\Chronos\Chronos;
use Curfle\Essence\Application;

/**
 * @method Event everyMinute()
 * @method Event everyTwoMinutes()
 * @method Event everyThreeMinutes()
 * @method Event everyFourMinutes()
 * @method Event everyFiveMinutes()
 * @method Event everyTenMinutes()
 * @method Event everyFifteenMinutes()
 * @method Event everyThirtyMinutes()
 * @method Event hourly()
 * @method Event hourlyAt(int $minutes)
 * @method Event everyTwoHours()
 * @method Event everyThreeHours()
 * @method Event everyFourHours()
 * @method Event everySixHours()
 * @method Event daily()
 * @method Event dailyAt(string $time)
 * @method Event weekly()
 * @method Event weeklyOn(int $day, string $time)
 * @method Event monthly()
 * @method Event monthlyOn(int $day, string $time)
 */
class Event
{

    /**
     * The event's resolver.
     *
     * @var Closure|string
     */
    private Closure|string $resolver;

    /**
     * The event's description.
     *
     * @var string
     */
    private string $description;

    /**
     * The application instance.
     *
     * @var Application
     */
    private Application $app;

    /**
     * The timetable instance.
     *
     * @var Timetable
     */
    private Timetable $timetable;


    /**
     * @param Application $app
     * @param Timetable $timetable
     */
    public function __construct(Application $app, Timetable $timetable)
    {
        $this->app = $app;
        $this->timetable = $timetable;
    }

    /**
     * Returns wether the event should be run or not.
     *
     * @param Chronos $timestamp
     * @return bool
     */
    public function isDue(Chronos $timestamp): bool
    {
        return $this->timetable->isDue($timestamp);
    }

    /**
     * Runs the event.
     *
     * @return void
     */
    public function run(): mixed
    {
        return $this->app->call($this->resolver);
    }

    /**
     * Return the event's description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the event's description.
     *
     * @param string $description
     * @return Event
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the event's resolver.
     *
     * @param Closure|string $resolver
     * @return Event
     */
    public function setResolver(Closure|string $resolver): static
    {
        $this->resolver = $resolver;
        return $this;
    }

    /**
     * Forward the call to the timetable instance.
     *
     * @param string $name
     * @param array $arguments
     * @return $this
     */
    public function __call(string $name, array $arguments): Event
    {
        $this->timetable->{$name}(...$arguments);
        return $this;
    }
}