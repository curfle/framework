<?php

namespace Curfle\Console\Commands;

use Curfle\Agreements\Console\Kernel as Buddy;
use Curfle\Chronos\Chronos;
use Curfle\Console\Command;
use Curfle\Console\Schedule;
use Curfle\Essence\Application;
use Curfle\Support\Str;
use Exception;

class ScheduleRunCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "schedule:run";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Runs the schedule.";


    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(Buddy $buddy, Schedule $schedule) {
        // freeze current time
        $now = Chronos::now();

        // collect the scheduled jobs
        $buddy->schedule($schedule);

        // run the schedule
        $result = $schedule->run($now);

        // write output
        $this->write(Str::trim($result->getContent()));
    }
}