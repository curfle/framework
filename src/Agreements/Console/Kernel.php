<?php

namespace Curfle\Agreements\Console;

use Curfle\Console\Input;
use Curfle\Console\Output;
use Curfle\Console\Schedule;
use Curfle\Essence\Application;

interface Kernel
{
    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap();

    /**
     * Register events, commands and functions that shall be executed in the future.
     *
     * @param Schedule $schedule
     */
    public function schedule(Schedule $schedule): void;

    /**
     * Handle an incoming Console input.
     *
     * @param Input $input
     * @return Output
     */
    public function run(Input $input): Output;

    /**
     * Terminate the application.
     *
     * @return mixed
     */
    public function terminate(): void;

    /**
     * Get the Curfle application instance.
     *
     * @return Application
     */
    public function getApplication(): Application;
}