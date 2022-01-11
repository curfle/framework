<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Command;
use Curfle\Support\Facades\Buddy;
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
    public function handle() {
        // write header
        $this->success("Successfully run schedule.");
    }
}