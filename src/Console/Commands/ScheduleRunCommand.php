<?php

namespace Curfle\Console\Commands;

use Curfle\Agreements\Console\Kernel as Buddy;
use Curfle\Console\Command;
use Curfle\Console\Schedule;
use Curfle\Essence\Application;
use Curfle\Support\Str;

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
     */
    public function handle(Buddy $buddy, Schedule $schedule) {
        // collect the scheduled jobs
        $buddy->schedule($schedule);

        // run the schedule
        $result = $schedule->run();

        // write output
        $this->write(Str::trim($result->getContent()));
    }
}