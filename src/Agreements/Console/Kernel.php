<?php

namespace Curfle\Agreements\Console;

use Curfle\Console\Input;
use Curfle\Console\Output;
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