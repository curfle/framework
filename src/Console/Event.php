<?php

namespace Curfle\Console;

use Closure;
use Curfle\Essence\Application;

class Event
{

    /**
     * The event's resolver.
     *
     * @var Closure|string
     */
    private Closure|string $resolver;

    /**
     * The application instance.
     *
     * @var Application
     */
    private Application $app;


    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Sets the event's resolver.
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
     * Returns wether the event should be run or not.
     *
     * @return bool
     */
    public function isDue(): bool
    {
        return true;
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
}