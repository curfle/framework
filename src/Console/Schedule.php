<?php

namespace Curfle\Console;

use Closure;
use Curfle\Chronos\Chronos;
use Curfle\Essence\Application;
use Curfle\Support\Str;

class Schedule
{

    /**
     * Holds all registered events.
     *
     * @var Event[]
     */
    private $events = [];

    /**
     * The application instance.
     *
     * @var Application
     */
    private Application $app;

    /**
     * The output instance.
     *
     * @var Output
     */
    private Output $output;


    public function __construct(Application $app, Output $output)
    {
        $this->app = $app;
        $this->output = $output;
    }

    /**
     * Add an event to the schedule.
     *
     * @param Event $event
     * @return Event
     */
    public function event(Event $event): Event
    {
        return $this->events[] = $event;
    }

    /**
     * Add a shell command to the schedule that will be run as a new process.
     *
     * @param string $command
     * @return Event
     */
    public function shell(string $command): Event
    {
        return $this->event($this->app->make(Event::class))
            ->setResolver(fn() => Shell::run($command))
            ->setDescription($command);
    }

    /**
     * Add a buddy command to the schedule that will be run as a new process.
     *
     * @param string $command
     * @return Event
     */
    public function command(string $command): Event
    {
        return $this->event($this->app->make(Event::class))
            ->setResolver(fn() => Shell::runCommand($command))
            ->setDescription("php buddy $command");
    }

    /**
     * Add a closure to the schedule that will be executed in the same application context as the current instance.
     *
     * @param Closure|string $resolver
     * @return Event
     */
    public function call(Closure|string $resolver): Event
    {
        return $this->event($this->app->make(Event::class))
            ->setResolver($resolver)
            ->setDescription($resolver instanceof Closure ? "Anonymous closure" : $resolver);
    }

    /**
     * Runs the schedule.
     *
     * @param Chronos $timestamp
     * @return Output
     */
    public function run(Chronos $timestamp): Output
    {
        $numberOfRunnedCommands = 0;
        foreach ($this->events as $event) {
            if ($event->isDue($timestamp)) {
                $this->output->write("Running scheduled event: " . $event->getDescription());
                $this->output->write((string)$event->run());
                $numberOfRunnedCommands++;
            }
        }
        if ($numberOfRunnedCommands > 0)
            $this->output->success($numberOfRunnedCommands. " event"
                . ($numberOfRunnedCommands > 1 ? "s have" : " has")
                . " run in total.");
        else {
            $this->output->success("No scheduled events are ready to run.");
        }
        return $this->output;
    }
}