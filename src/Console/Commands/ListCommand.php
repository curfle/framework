<?php

namespace Curfle\Console\Commands;

use Closure;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\Support\Facades\Buddy;

class ListCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function install()
    {
        $this->signature("list")
            ->description("Lists all commands")
            ->resolver(function () {
                // write header
                $this->write("All commands:");

                // get all commands
                $commands = Buddy::getAllCommands();

                // sort commands by signature
                usort($commands, fn($a, $b) =>  strcmp($a->getSignature(), $b->getSignature()));

                // print command information
                foreach ($commands as $command)
                    $this->write(" - ". $command->getSignature());
            });
    }
}