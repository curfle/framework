<?php

namespace Curfle\Console;

use Curfle\Essence\Application as CurfleApplication;
use Curfle\Support\Exceptions\Console\CommandNotFoundException;

class Application
{

    /**
     * The application instance.
     *
     * @var CurfleApplication
     */
    private CurfleApplication $app;

    /**
     * List of all available commands.
     *
     * @var Command[]
     */
    private array $commands = [];

    /**
     * @param CurfleApplication $app
     */
    public function __construct(CurfleApplication $app)
    {
        $this->app = $app;
    }

    /**
     * Adds a new command to the application.
     *
     * @param Command $command
     * @return Command
     */
    public function add(Command $command): Command
    {
        return $this->commands[] = $command;
    }

    /**
     * Runs a command.
     *
     * @param Input $input
     * @return Output
     * @throws CommandNotFoundException
     */
    public function run(Input $input): Output
    {
        foreach($this->commands as $command){
            if($command->matches($input)){
                foreach ($command->getMatchedParameters() as $name => $value) {
                    $input->addNamedArgument($name, $value);
                }
                return $command->run($input);
            }
        }

        throw new CommandNotFoundException("Command [{$input->input()}] could not be resolved, as no matching signature was found.");
    }
}