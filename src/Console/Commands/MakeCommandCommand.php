<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeCommandCommand extends MakeCommand
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:command {name}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Creates a new command class.";

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle(Input $input) {
        // get name and create file
        $name = "App\\Console\\Commands\\" . $input->argument("name");
        $filename = $this->app->basePath("app/Console/Commands/") . $this->createFileName($name);
        $this->makeFile(
            $name,
            $filename
        );
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Command.template";
    }
}