<?php

namespace Curfle\Console\Commands;

use Curfle\Chronos\Chronos;
use Curfle\Console\Command;
use Curfle\Console\Input;
use Curfle\Console\Shell;
use Curfle\Support\Facades\Buddy;
use Curfle\Support\Str;
use Exception;

class ScheduleWorkCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "schedule:work";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Emulates a local cron command and runs the schedule every minute.";

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        // determine start
        $nextRun = $this->getNextMinute();
        while (true) {
            // sleep until next minute
            time_sleep_until($nextRun->unix());

            // write current timestamp
            $this->write(Chronos::now()->get() . ": running schedule")->flush();

            // execute command and write result
            $output = $this->executeSchedule();
            $this->write($output)->flush();

            // determine next run
            $nextRun = $this->getNextMinute($nextRun);
        }

    }

    /**
     * Executes the `schedule:run` command.
     *
     * @return string
     */
    private function executeSchedule(): string
    {
        return (string)Shell::runCommand("schedule:run");
    }

    /**
     * Returns the next minute.
     *
     * @param Chronos|null $lastMinute
     * @return Chronos
     * @throws Exception
     */
    private function getNextMinute(Chronos $lastMinute = null): Chronos
    {
        if ($lastMinute === null)
            $lastMinute = Chronos::now();

        // set seconds to zero
        $lastMinute->setTime($lastMinute->hour(), $lastMinute->minute(), 0);

        return $lastMinute->modify("+1 minute");
    }
}