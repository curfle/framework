<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeGuardianCommand extends MakeCommand
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:guardian {name}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Creates a new guardian class.";

    /**
     * Execute the console command.
     */
    public function handle(Input $input) {
        // get name and create file
        $name = "App\\Auth\\Guardians\\" . $input->argument("name");
        $filename = $this->app->basePath("app/Auth/Guardians/") . $this->createFileName($name);
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
        return __DIR__ . "/../Templates/Guardian.template";
    }
}