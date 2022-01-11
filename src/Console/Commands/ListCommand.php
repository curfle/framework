<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Command;
use Curfle\Support\Facades\Buddy;
use Curfle\Support\Str;

class ListCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "list";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Lists all commands.";

    /**
     * Execute the console command.
     */
    public function handle() {
        // write header
        $this->write("All commands:");

        // get all commands
        $commands = Buddy::getAllCommands();

        // sort commands by signature
        usort($commands, fn($a, $b) =>  strcmp($a->getSignature(), $b->getSignature()));

        // print command information
        foreach ($commands as $command){
            $signature = $command->getSignature();
            $description = $command->getDescription();
            $this->write(" - $signature");
            if(!Str::empty($description))
                $this->write("    ". $description);
        }
    }
}