<?php

namespace Curfle\Console;

use Curfle\Essence\Application;
use Curfle\FileSystem\FileSystem;
use Curfle\Support\Exceptions\Console\CommandNotFoundException;
use Curfle\Utilities\Utilities;
use ReflectionException;

class Buddy
{

    /**
     * The Application instance
     *
     * @var Application
     */
    private Application $app;

    /**
     * The FileSystem instance
     *
     * @var FileSystem
     */
    private FileSystem $files;

    /**
     * List of all available commands.
     *
     * @var Command[]
     */
    private array $commands = [];

    /**
     * @param Application $app
     * @param FileSystem $files
     */
    public function __construct(Application $app, FileSystem $files)
    {
        $this->app = $app;
        $this->files = $files;
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
     * Loads commands from directory.
     *
     * @param string $directory
     * @throws ReflectionException
     */
    public function loadFromDirectory(string $directory)
    {
        if (!$this->files->isDirectory($directory))
            return;

        foreach ($this->files->files($directory) as $file) {
            $path = $directory . DIRECTORY_SEPARATOR . $file;
            $class = Utilities::getClassNameFromFile($path);

            if(is_subclass_of($class, Command::class)
                && !(new \ReflectionClass($class))->isAbstract())
                $this->add($this->app->make($class));
        }
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
        // check if command is in registered commands
        foreach($this->commands as $command){
            if($command->matches($input)){
                foreach ($command->getMatchedParameters() as $name => $value) {
                    $input->addArgument($name, $value);
                }
                return $command->newOutput()->run();
            }
        }

        throw new CommandNotFoundException("Command [{$input->input()}] could not be resolved, as no matching signature was found.");
    }

    /**
     * Returns all commands.
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}